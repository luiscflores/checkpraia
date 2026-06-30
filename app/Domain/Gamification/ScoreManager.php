<?php

namespace App\Domain\Gamification;

use App\Models\User;
use App\Models\FlagReport;
use App\Models\ScoreTransaction;
use App\Models\Referral;
use Illuminate\Support\Facades\DB;

class ScoreManager
{
    private const BASE_REPORT_POINTS = 1;
    private const FIRST_REPORT_BONUS = 1;
    private const PENALTY_POINTS = -2;
    private const PENALTY_FLOOR = 0;

    private const VOTE_WEIGHT_MIN_ACCEPTED = 50;
    private const VOTE_WEIGHT_SUCCESS_RATE = 0.90;
    private const VOTE_WEIGHT_NORMAL = 1;
    private const VOTE_WEIGHT_REINFORCED = 2;

    private const REFERRALS_PER_BONUS = 5;
    private const REFERRAL_BONUS_POINTS = 10;

    public function addReportPoints(FlagReport $report): void
    {
        $user = $report->user;

        $points = self::BASE_REPORT_POINTS;
        $isFirst = $this->isFirstReportOfDay($report);

        if ($isFirst) {
            $points += self::FIRST_REPORT_BONUS;
        }

        DB::transaction(function () use ($user, $report, $points, $isFirst) {
            ScoreTransaction::create([
                'user_id' => $user->id,
                'flag_report_id' => $report->id,
                'type' => $isFirst ? 'first_report_bonus' : 'report_accepted',
                'points' => $points,
                'status' => 'confirmed',
                'description' => ($isFirst ? 'Primeira confirmação aceite do dia na ' : 'Confirmação aceite na ') . $report->beach->name,
            ]);

            $user->score += $points;
            $user->confirmations_count += 1;
            $user->accepted_confirmations_count += 1;
            $user->save();
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
                'points' => self::PENALTY_POINTS,
                'status' => 'confirmed',
                'description' => 'Confirmação penalizada na ' . $report->beach->name,
            ]);

            $user->score = max(self::PENALTY_FLOOR, $user->score + self::PENALTY_POINTS);
            $user->confirmations_count += 1;
            $user->penalized_confirmations_count += 1;
            $user->save();
        });
    }

    public function getVoteWeight(User $user): int
    {
        if ($user->is_suspended) {
            return 0;
        }

        if ($user->accepted_confirmations_count >= self::VOTE_WEIGHT_MIN_ACCEPTED) {
            $totalConfirmations = $user->confirmations_count ?: 1;
            $successRate = $user->accepted_confirmations_count / $totalConfirmations;

            if ($successRate >= self::VOTE_WEIGHT_SUCCESS_RATE) {
                return self::VOTE_WEIGHT_REINFORCED;
            }
        }

        return self::VOTE_WEIGHT_NORMAL;
    }

    public function processReferral(User $invitedUser): void
    {
        $referral = Referral::where('invited_user_id', $invitedUser->id)
            ->where('status', 'pending')
            ->first();

        if (!$referral) {
            return;
        }

        if ($invitedUser->accepted_confirmations_count >= 1) {
            DB::transaction(function () use ($referral, $invitedUser) {
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

                $deservedBonusesCount = (int) floor($totalQualifiedCount / self::REFERRALS_PER_BONUS);

                if ($deservedBonusesCount > $paidBonusesCount) {
                    $bonusesToPay = $deservedBonusesCount - $paidBonusesCount;
                    $pointsToGrant = $bonusesToPay * self::REFERRAL_BONUS_POINTS;

                    ScoreTransaction::create([
                        'user_id' => $referrer->id,
                        'referral_id' => $referral->id,
                        'type' => 'referral_bonus',
                        'points' => $pointsToGrant,
                        'status' => 'confirmed',
                        'description' => 'Bónus por convidar ' . ($bonusesToPay * self::REFERRALS_PER_BONUS) . ' amigos válidos',
                    ]);

                    $referrer->score += $pointsToGrant;
                    $referrer->save();
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

        return !$exists;
    }
}
