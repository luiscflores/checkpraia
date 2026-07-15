<?php

namespace App\Domain\Gamification;

use App\Models\FlagReport;
use App\Models\Referral;
use App\Models\ScoreTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ScoreManager
{
    public function addReportPoints(FlagReport $report): void
    {
        $user = $report->user;

        $points = config('gamification.points.base_report');
        $isFirst = $this->isFirstReportOfDay($report);

        if ($isFirst) {
            $points += config('gamification.points.first_report_bonus');
        }

        DB::transaction(function () use ($user, $report, $points, $isFirst) {
            ScoreTransaction::create([
                'user_id' => $user->id,
                'flag_report_id' => $report->id,
                'type' => $isFirst ? 'first_report_bonus' : 'report_accepted',
                'points' => $points,
                'status' => 'confirmed',
                'description' => ($isFirst ? 'Primeira confirmação aceite do dia na ' : 'Confirmação aceite na ').$report->beach->name,
            ]);

            $user->increment('score', $points);
            $user->increment('confirmations_count');
            $user->increment('accepted_confirmations_count');
        });

        $this->processReferral($user);
    }

    public function penalizeReport(FlagReport $report): void
    {
        $user = $report->user;

        DB::transaction(function () use ($user, $report) {
            ScoreTransaction::create([
                'user_id' => $user->id,
                'flag_report_id' => $report->id,
                'type' => 'report_penalized',
                'points' => config('gamification.points.penalty'),
                'status' => 'confirmed',
                'description' => 'Confirmação penalizada na '.$report->beach->name,
            ]);

            $penalty = config('gamification.points.penalty');
            $floor = config('gamification.points.penalty_floor');
            $user->update([
                'score' => DB::raw("GREATEST(COALESCE(score, 0) + ({$penalty}), {$floor})"),
            ]);
            $user->increment('confirmations_count');
            $user->increment('penalized_confirmations_count');
        });
    }

    public function getVoteWeight(User $user): int
    {
        if ($user->is_suspended) {
            return 0;
        }

        if ($user->accepted_confirmations_count >= config('gamification.vote_weight.min_accepted')) {
            $totalConfirmations = $user->confirmations_count ?: 1;
            $successRate = $user->accepted_confirmations_count / $totalConfirmations;

            if ($successRate >= config('gamification.vote_weight.success_rate')) {
                return config('gamification.vote_weight.reinforced');
            }
        }

        return config('gamification.vote_weight.normal');
    }

    public function processReferral(User $invitedUser): void
    {
        $referral = Referral::where('invited_user_id', $invitedUser->id)
            ->where('status', 'pending')
            ->first();

        if (! $referral) {
            return;
        }

        if ($invitedUser->accepted_confirmations_count >= 1) {
            DB::transaction(function () use ($referral) {
                $referral->status = 'qualified';
                $referral->qualified_at = now();
                $referral->save();

                $referrer = $referral->referrer;

                $totalQualifiedCount = Referral::where('referrer_user_id', $referrer->id)
                    ->where('status', 'qualified')
                    ->count();

                $paidBonusesCount = ScoreTransaction::where('user_id', $referrer->id)
                    ->where('type', 'referral_bonus')
                    ->count();

                $deservedBonusesCount = (int) floor($totalQualifiedCount / config('gamification.referrals.per_bonus'));

                if ($deservedBonusesCount > $paidBonusesCount) {
                    $bonusesToPay = $deservedBonusesCount - $paidBonusesCount;
                    $pointsToGrant = $bonusesToPay * config('gamification.referrals.bonus_points');

                    ScoreTransaction::create([
                        'user_id' => $referrer->id,
                        'referral_id' => $referral->id,
                        'type' => 'referral_bonus',
                        'points' => $pointsToGrant,
                        'status' => 'confirmed',
                        'description' => 'Bónus por convidar '.($bonusesToPay * config('gamification.referrals.per_bonus')).' amigos válidos',
                    ]);

                    $referrer->increment('score', $pointsToGrant);
                }
            });
        }
    }

    private function isFirstReportOfDay(FlagReport $report): bool
    {
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        $exists = FlagReport::where('beach_id', $report->beach_id)
            ->where('id', '!=', $report->id)
            ->whereBetween('reported_at', [$todayStart, $todayEnd])
            ->where('status', '!=', 'cancelled')
            ->exists();

        return ! $exists;
    }
}
