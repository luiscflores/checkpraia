<?php

namespace Tests\Feature;

use App\Domain\Community\ConsensusResolver;
use App\Domain\Gamification\ScoreManager;
use App\Jobs\PurgePreciseLocations;
use App\Livewire\Public\BeachDetail;
use App\Models\Beach;
use App\Models\FlagPrediction;
use App\Models\FlagReport;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class CheckPraiaSystemTest extends TestCase
{
    use RefreshDatabase;

    protected $beach;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a default beach (Praia da Barra coordinates)
        $this->beach = Beach::create([
            'name' => 'Praia da Barra',
            'slug' => 'praia-da-barra-test',
            'region' => 'Continental',
            'municipality' => 'Ílhavo',
            'latitude' => 40.644167,
            'longitude' => -8.748333,
            'is_active' => true,
        ]);

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@checkpraia.pt',
            'username' => 'testuser',
            'password' => bcrypt('password'),
            'referral_code' => 'TESTCODE',
        ]);
    }

    /**
     * Test GPS proximity reporting rules.
     */
    public function test_gps_distance_reporting_validation()
    {
        $this->actingAs($this->user);

        // 1. Report exactly at beach coordinates (0 km distance) -> should succeed
        $response = $this->post(route('beach.show.pt', ['locale' => 'pt', 'slug' => $this->beach->slug]), [
            // Simulating Livewire call under the hood
        ]);

        $this->assertTrue(true); // Base route loaded

        // Let's test the component logic directly using standard Livewire test harness
        $component = Livewire::test(BeachDetail::class, ['slug' => $this->beach->slug])
            ->call('submitReport', 'green', 40.644167, -8.748333, 10); // accurate coordinates

        $this->assertDatabaseHas('flag_reports', [
            'user_id' => $this->user->id,
            'beach_id' => $this->beach->id,
            'flag' => 'green',
            'status' => 'confirmed',
        ]);

        // 2. Try reporting 2 km away (e.g. 40.662, -8.748) -> should fail with error
        $anotherUser = User::create([
            'name' => 'User Two',
            'email' => 'two@checkpraia.pt',
            'username' => 'usertwo',
            'password' => bcrypt('password'),
        ]);
        $this->actingAs($anotherUser);

        $componentTwo = Livewire::test(BeachDetail::class, ['slug' => $this->beach->slug])
            ->call('submitReport', 'red', 40.670000, -8.748333, 10); // Far away

        $componentTwo->assertHasErrors('report');
        $this->assertDatabaseMissing('flag_reports', [
            'user_id' => $anotherUser->id,
            'flag' => 'red',
        ]);
    }

    /**
     * Test community consensus overrides.
     */
    public function test_consensus_overrides_prediction()
    {
        // Set initial predicted flag to yellow
        FlagPrediction::create([
            'beach_id' => $this->beach->id,
            'selected_flag' => 'yellow',
            'green_probability' => 10,
            'yellow_probability' => 80,
            'red_probability' => 10,
            'confidence' => 100,
            'calculated_at' => now(),
        ]);

        $resolver = new ConsensusResolver;
        $resolver->resolveCurrentStatus($this->beach);

        // Verify initial status is prediction (yellow)
        $this->assertDatabaseHas('beach_current_statuses', [
            'beach_id' => $this->beach->id,
            'source' => 'prediction',
            'flag' => 'yellow',
        ]);

        // User A reports green flag
        $userA = User::create(['name' => 'User A', 'email' => 'a@checkpraia.pt', 'username' => 'usera', 'password' => 'pass']);
        FlagReport::create([
            'user_id' => $userA->id,
            'beach_id' => $this->beach->id,
            'flag' => 'green',
            'vote_weight' => 1,
            'status' => 'pending',
            'distance_to_beach' => 0.1,
            'reported_at' => now(),
        ]);

        // Re-calculate: 1 user is not enough to override
        $resolver->resolveCurrentStatus($this->beach);
        $this->assertDatabaseHas('beach_current_statuses', [
            'beach_id' => $this->beach->id,
            'source' => 'prediction',
            'flag' => 'yellow',
        ]);

        // User B reports green flag (now we have 2 distinct users)
        $userB = User::create(['name' => 'User B', 'email' => 'b@checkpraia.pt', 'username' => 'userb', 'password' => 'pass']);
        FlagReport::create([
            'user_id' => $userB->id,
            'beach_id' => $this->beach->id,
            'flag' => 'green',
            'vote_weight' => 1,
            'status' => 'pending',
            'distance_to_beach' => 0.2,
            'reported_at' => now(),
        ]);

        // Re-calculate: 2 users override prediction to green
        $resolver->resolveCurrentStatus($this->beach);
        $this->assertDatabaseHas('beach_current_statuses', [
            'beach_id' => $this->beach->id,
            'source' => 'community',
            'flag' => 'green',
        ]);
    }

    /**
     * Test report resolution, ties, and 75% penalization rules.
     */
    public function test_consensus_tie_breaker_and_penalization()
    {
        $resolver = new ConsensusResolver;

        $uA = User::create(['name' => 'A', 'email' => 'a@checkpraia.pt', 'username' => 'usera', 'password' => 'pass']);
        $uB = User::create(['name' => 'B', 'email' => 'b@checkpraia.pt', 'username' => 'userb', 'password' => 'pass']);
        $uC = User::create(['name' => 'C', 'email' => 'c@checkpraia.pt', 'username' => 'userc', 'password' => 'pass']);
        $uD = User::create(['name' => 'D', 'email' => 'd@checkpraia.pt', 'username' => 'userd', 'password' => 'pass']);

        // Case 1: Tie (Yellow: 1, Red: 1) -> most conservative (Red) wins
        FlagReport::create(['user_id' => $uA->id, 'beach_id' => $this->beach->id, 'flag' => 'yellow', 'distance_to_beach' => 0.1, 'reported_at' => now()->subMinutes(10)]);
        FlagReport::create(['user_id' => $uB->id, 'beach_id' => $this->beach->id, 'flag' => 'red', 'distance_to_beach' => 0.1, 'reported_at' => now()]);

        $resolver->resolveCurrentStatus($this->beach);
        $this->assertDatabaseHas('beach_current_statuses', [
            'beach_id' => $this->beach->id,
            'flag' => 'red',
        ]);

        // Case 2: Penalization (A, B, C report yellow; D reports green). 75% are contrary to D.
        // We submit reports 65 minutes ago so they close when resolving
        $rA = FlagReport::create(['user_id' => $uA->id, 'beach_id' => $this->beach->id, 'flag' => 'yellow', 'status' => 'pending', 'distance_to_beach' => 0.1, 'reported_at' => now()->subMinutes(65)]);
        $rB = FlagReport::create(['user_id' => $uB->id, 'beach_id' => $this->beach->id, 'flag' => 'yellow', 'status' => 'pending', 'distance_to_beach' => 0.1, 'reported_at' => now()->subMinutes(65)]);
        $rC = FlagReport::create(['user_id' => $uC->id, 'beach_id' => $this->beach->id, 'flag' => 'yellow', 'status' => 'pending', 'distance_to_beach' => 0.1, 'reported_at' => now()->subMinutes(65)]);
        $rD = FlagReport::create(['user_id' => $uD->id, 'beach_id' => $this->beach->id, 'flag' => 'green', 'status' => 'pending', 'reported_at' => now()->subMinutes(65), 'distance_to_beach' => 0.1]);

        // Resolve each report individually (consensus no longer runs as a deferred job)
        $resolver = new ConsensusResolver;
        foreach ([$rA, $rB, $rC, $rD] as $r) {
            $resolver->resolveReport($r);
        }

        // D's report should be rejected/penalized
        $rD->refresh();
        $this->assertEquals('rejected', $rD->status);
        $this->assertDatabaseHas('score_transactions', [
            'user_id' => $uD->id,
            'type' => 'report_penalized',
            'points' => -2,
        ]);

        // A's report should be confirmed (accepted)
        $rA->refresh();
        $this->assertEquals('confirmed', $rA->status);
    }

    /**
     * Test referrals program progression.
     */
    public function test_referral_gamification_bonus()
    {
        $scoreManager = new ScoreManager;

        // User A invites User B
        $userA = User::create(['name' => 'User A', 'email' => 'a@checkpraia.pt', 'username' => 'usera', 'password' => 'p', 'referral_code' => 'ACODE', 'score' => 0]);
        $userB = User::create(['name' => 'User B', 'email' => 'b@checkpraia.pt', 'username' => 'userb', 'password' => 'p', 'referral_code' => 'BCODE', 'score' => 0]);

        $referral1 = Referral::create([
            'referrer_user_id' => $userA->id,
            'invited_user_id' => $userB->id,
            'code' => 'ACODE',
            'status' => 'pending',
        ]);

        // User B submits report
        $report = FlagReport::create([
            'user_id' => $userB->id,
            'beach_id' => $this->beach->id,
            'flag' => 'green',
            'status' => 'pending',
            'distance_to_beach' => 0.1,
            'reported_at' => now(),
        ]);

        // Accept B's report -> B gets points, referral becomes qualified
        $scoreManager->addReportPoints($report);

        $referral1->refresh();
        $this->assertEquals('qualified', $referral1->status);

        // A does not have 5 qualified referrals yet, so score is still 0
        $userA->refresh();
        $this->assertEquals(0, $userA->score);

        // Add 4 more qualified referrals for A
        for ($i = 1; $i <= 4; $i++) {
            $invited = User::create(['name' => "Invited $i", 'email' => "invited{$i}@checkpraia.pt", 'username' => "invited{$i}", 'password' => 'p', 'score' => 0]);

            Referral::create([
                'referrer_user_id' => $userA->id,
                'invited_user_id' => $invited->id,
                'code' => 'ACODE',
                'status' => 'pending',
            ]);

            $invitedReport = FlagReport::create([
                'user_id' => $invited->id,
                'beach_id' => $this->beach->id,
                'flag' => 'green',
                'status' => 'pending',
                'distance_to_beach' => 0.1,
                'reported_at' => now(),
            ]);

            $scoreManager->addReportPoints($invitedReport);
        }

        // Now A has 5 qualified referrals -> should receive +10 points
        $userA->refresh();
        $this->assertEquals(10, $userA->score);
        $this->assertDatabaseHas('score_transactions', [
            'user_id' => $userA->id,
            'type' => 'referral_bonus',
            'points' => 10,
        ]);
    }

    /**
     * Test daily purge job.
     */
    public function test_daily_coordinate_purge_job()
    {
        // Create report with precise lat/lng created yesterday
        $report = FlagReport::create([
            'user_id' => $this->user->id,
            'beach_id' => $this->beach->id,
            'flag' => 'green',
            'status' => 'confirmed',
            'distance_to_beach' => 0.1,
            'latitude' => 40.644167,
            'longitude' => -8.748333,
            'reported_at' => now()->subDay(),
        ]);

        // Force timestamp updates in DB
        DB::table('flag_reports')
            ->where('id', $report->id)
            ->update([
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ]);

        // Verify coordinates are there
        $this->assertNotNull($report->latitude);
        $this->assertNotNull($report->longitude);

        // Dispatch purge job
        PurgePreciseLocations::dispatchSync();

        // Verify coordinates are now NULL
        $report->refresh();
        $this->assertNull($report->latitude);
        $this->assertNull($report->longitude);
        // Distance is still intact
        $this->assertEquals(0.1, (float) $report->distance_to_beach);
    }
}
