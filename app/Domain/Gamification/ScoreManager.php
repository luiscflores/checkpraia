<?php

namespace App\Domain\Gamification;

use App\Models\User;
use App\Models\FlagReport;
use App\Models\ScoreTransaction;
use App\Models\Referral;
use Illuminate\Support\Facades\DB;

class ScoreManager
{
    /**
     * Add points for an accepted report.
     */
    public function addReportPoints(FlagReport $report): void
    {
        $user = $report->user;
        $beach = $report->beach;

        // Base points for confirmation is +1
        $points = 1;
        $isFirst = $this->isFirstReportOfDay($report);
        
        if ($isFirst) {
            $points += 1; // +1 additional point for first confirmation of the day
        }

        DB::transaction(function () use ($user, $report, $points, $isFirst) {
            // Create transaction log
            ScoreTransaction::create([
                'user_id' => $user->id,
                'flag_report_id' => $report->id,
                'type' => $isFirst ? 'first_report_bonus' : 'report_accepted',
                'points' => $points,
                'status' => 'confirmed',
                'description' => $isFirst 
                    ? "Primeira confirmação aceite do dia na " . $report->beach->name 
                    : "Confirmação aceite na " . $report->beach->name,
            ]);

            // Update user counters and score
            $user->score += $points;
            $user->confirmations_count += 1;
            $user->accepted_confirmations_count += 1;
            $user->save();
        });

        // Trigger referral progression check
        $this->processReferral($user);
    }

    /**
     * Penalize a user for an incorrect report.
     */
    public function penalizeReport(FlagReport $report): void
    {
        $user = $report->user;

        DB::transaction(function () use ($user, $report) {
            // Create transaction log
            ScoreTransaction::create([
                'user_id' => $user->id,
                'flag_report_id' => $report->id,
                'type' => 'report_penalized',
                'points' => -2,
                'status' => 'confirmed',
                'description' => "Confirmação penalizada na " . $report->beach->name,
            ]);

            // Update user counters and score
            $user->score = max(0, $user->score - 2);
            $user->confirmations_count += 1;
            $user->penalized_confirmations_count += 1;
            $user->save();
        });
    }

    /**
     * Compute the user's vote weight based on experience.
     */
    public function getVoteWeight(User $user): int
    {
        if ($user->is_suspended) {
            return 0;
        }

        // Must have at least 50 accepted confirmations
        // Must have at least a 90% success rate (accepted / total confirmations)
        if ($user->accepted_confirmations_count >= 50) {
            $totalConfirmations = $user->confirmations_count ?: 1;
            $successRate = $user->accepted_confirmations_count / $totalConfirmations;

            if ($successRate >= 0.90) {
                return 2; // Reinforced vote weight
            }
        }

        return 1; // Standard weight
    }

    /**
     * Check if B B was invited, validate B B's first accepted confirmation, and issue rewards to referrer.
     */
    public function processReferral(User $invitedUser): void
    {
        // Check if there is a pending referral invitation for this user
        $referral = Referral::where('invited_user_id', $invitedUser->id)
            ->where('status', 'pending')
            ->first();

        if (!$referral) {
            return;
        }

        // User must have at least one accepted report to qualify the referral
        if ($invitedUser->accepted_confirmations_count >= 1) {
            DB::transaction(function () use ($referral, $invitedUser) {
                // Update referral status
                $referral->status = 'qualified';
                $referral->qualified_at = now();
                $referral->save();

                $referrer = $referral->referrer;

                // Count qualified referrals for this referrer that have not been paid yet
                // Each block of 5 qualified referrals gives +10 points
                $totalQualifiedCount = Referral::where('referrer_user_id', $referrer->id)
                    ->where('status', 'qualified')
                    ->count();

                // Check how many referral bonuses have already been issued
                $paidBonusesCount = ScoreTransaction::where('user_id', $referrer->id)
                    ->where('type', 'referral_bonus')
                    ->count();

                $deservedBonusesCount = (int) floor($totalQualifiedCount / 5);

                if ($deservedBonusesCount > $paidBonusesCount) {
                    $bonusesToPay = $deservedBonusesCount - $paidBonusesCount;
                    $pointsToGrant = $bonusesToPay * 10;

                    ScoreTransaction::create([
                        'user_id' => $referrer->id,
                        'referral_id' => $referral->id,
                        'type' => 'referral_bonus',
                        'points' => $pointsToGrant,
                        'status' => 'confirmed',
                        'description' => "Bónus por convidar " . ($bonusesToPay * 5) . " amigos válidos",
                    ]);

                    $referrer->score += $pointsToGrant;
                    $referrer->save();
                }
            });
        }
    }

    /**
     * Detect if this report is the first accepted report on the specified beach for today.
     */
    private function isFirstReportOfDay(FlagReport $report): bool
    {
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        // Check if there is any other report on this beach today that is already accepted
        $exists = FlagReport::where('beach_id', $report->beach_id)
            ->where('id', '!=', $report->id)
            ->whereBetween('reported_at', [$todayStart, $todayEnd])
            ->where('status', 'confirmed')
            ->exists();

        return !$exists;
    }
}
