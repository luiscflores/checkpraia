<?php

namespace App\Domain\Community;

use App\Models\Beach;
use App\Models\FlagReport;
use App\Models\BeachCurrentStatus;
use App\Models\FlagPrediction;
use App\Models\OfficialAlert;
use App\Models\User;
use App\Domain\Gamification\ScoreManager;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ConsensusResolver
{
    protected $scoreManager;

    public function __construct()
    {
        $this->scoreManager = new ScoreManager();
    }

    /**
     * Update the current active flag cache for a beach based on alerts, community consensus, or forecasts.
     */
    public function resolveCurrentStatus(Beach $beach): BeachCurrentStatus
    {
        $status = BeachCurrentStatus::firstOrNew(['beach_id' => $beach->id]);

        // 1. Check for official alerts (takes absolute priority)
        $alert = OfficialAlert::where('beach_id', $beach->id)
            ->orWhereNull('beach_id')
            ->where('started_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('ended_at')->orWhere('ended_at', '>=', now());
            })->first();

        if ($alert && in_array($alert->type, ['interdiction', 'restriction'])) {
            $status->fill([
                'source' => 'alert',
                'flag' => 'red',
                'confidence' => 100,
                'consensus_reports_count' => 0,
                'reason' => 'Interdição oficial da autoridade: ' . $alert->description,
            ]);
            $status->save();
            return $status;
        }

        // 2. Check for active community reports in the last 60 minutes
        $oneHourAgo = now()->subMinutes(60);
        $activeReports = FlagReport::where('beach_id', $beach->id)
            ->where('reported_at', '>=', $oneHourAgo)
            ->where('status', '!=', 'cancelled')
            ->get();

        $distinctUsersCount = $activeReports->pluck('user_id')->unique()->count();

        // Community flag overrides prediction if there are at least 2 distinct users
        if ($distinctUsersCount >= 2) {
            $votes = ['green' => 0, 'yellow' => 0, 'red' => 0];
            $latestReportTime = null;
            $latestReportFlag = null;

            foreach ($activeReports as $report) {
                $votes[$report->flag] += $report->vote_weight;
                
                if (is_null($latestReportTime) || $report->reported_at->gt($latestReportTime)) {
                    $latestReportTime = $report->reported_at;
                    $latestReportFlag = $report->flag;
                }
            }

            // Find the winning flag color
            $maxVotes = max($votes);
            $winners = [];
            foreach ($votes as $color => $count) {
                if ($count === $maxVotes) {
                    $winners[] = $color;
                }
            }

            $winningFlag = null;
            if (count($winners) === 1) {
                $winningFlag = $winners[0];
            } else {
                // Tie breaker:
                // 1. Use the most recent report color if it is in the winners list
                if (in_array($latestReportFlag, $winners)) {
                    $winningFlag = $latestReportFlag;
                } else {
                    // 2. Use the most conservative color (red > yellow > green)
                    if (in_array('red', $winners)) {
                        $winningFlag = 'red';
                    } elseif (in_array('yellow', $winners)) {
                        $winningFlag = 'yellow';
                    } else {
                        $winningFlag = 'green';
                    }
                }
            }

            $status->fill([
                'source' => 'community',
                'flag' => $winningFlag,
                'confidence' => 95,
                'consensus_reports_count' => $activeReports->count(),
                'reason' => 'Confirmado por utilizadores locais (' . $distinctUsersCount . ' votos na última hora).',
            ]);
            $status->save();
            return $status;
        }

        // 3. Fallback to automatic prediction
        $prediction = FlagPrediction::where('beach_id', $beach->id)->orderBy('calculated_at', 'desc')->first();
        if ($prediction) {
            $reason = 'Condições favoráveis de vento, ondulação e qualidade da água.';
            
            $ocean = \App\Models\OceanForecast::where('beach_id', $beach->id)->orderBy('forecasted_at', 'desc')->first();
            $weather = \App\Models\WeatherForecast::where('beach_id', $beach->id)->orderBy('forecasted_at', 'desc')->first();
            $quality = \App\Models\WaterQualitySnapshot::where('beach_id', $beach->id)->orderBy('sampled_at', 'desc')->first();
            
            if ($prediction->selected_flag === 'red') {
                if ($quality && strtolower($quality->quality_class) === 'poor') {
                    $reason = 'Qualidade da água imprópria para banhos.';
                } elseif ($ocean && $ocean->wave_height_max > 2.0) {
                    $reason = 'Ondulação muito forte com ondas superiores a ' . $ocean->wave_height_max . 'm (Mar perigoso).';
                } elseif ($weather && $weather->wind_speed > 22.0) {
                    $reason = 'Vento extremamente forte de ' . $weather->wind_speed . ' nós (Perigo de correntes).';
                } else {
                    $reason = 'Previsão automática indica condições gerais perigosas.';
                }
            } elseif ($prediction->selected_flag === 'yellow') {
                if ($quality && strtolower($quality->quality_class) === 'sufficient') {
                    $reason = 'Qualidade da água apenas aceitável para banhos.';
                } elseif ($ocean && $ocean->wave_height_max > 1.2) {
                    $reason = 'Ondulação moderada com ondas de até ' . $ocean->wave_height_max . 'm (Recomenda-se precaução).';
                } elseif ($weather && $weather->wind_speed > 14.0) {
                    $reason = 'Vento moderado a forte de ' . $weather->wind_speed . ' nós.';
                } elseif ($beach->features && $beach->features->current_risk === 'high') {
                    $reason = 'Risco elevado de correntes permanentes nesta praia.';
                } else {
                    $reason = 'Condições marítimas requerem atenção reforçada.';
                }
            } else {
                if ($ocean && $ocean->wave_height_max < 0.6) {
                    $reason = 'Mar calmo (' . $ocean->wave_height_max . 'm) e vento fraco.';
                } else {
                    $reason = 'Condições favoráveis de vento, ondulação e qualidade da água.';
                }
            }

            $status->fill([
                'source' => 'prediction',
                'flag' => $prediction->selected_flag,
                'confidence' => $prediction->confidence,
                'consensus_reports_count' => 0,
                'reason' => $reason,
            ]);
        } else {
            // No prediction, show gray (no info)
            $status->fill([
                'source' => 'prediction',
                'flag' => 'gray',
                'confidence' => 0,
                'consensus_reports_count' => 0,
                'reason' => 'Sem informações de previsão ou relatórios disponíveis.',
            ]);
        }

        // 4. Out of season check (if current date is outside season_start to season_end)
        $today = now()->toDateString();
        if ($beach->season_start && $beach->season_end) {
            if ($today < $beach->season_start->toDateString() || $today > $beach->season_end->toDateString()) {
                $status->flag = 'blue_or_neutral'; // Out of season flag color
                $status->reason = 'Fora da época balnear oficial.';
            }
        }

        $status->save();
        return $status;
    }

    /**
     * Resolve a report after its 60-minute window has closed to determine reward or penalization.
     */
    public function resolveReport(FlagReport $report): void
    {
        if ($report->status !== 'pending') {
            return;
        }

        $beach = $report->beach;
        $windowStart = $report->reported_at;
        $windowEnd = $report->reported_at->copy()->addMinutes(60);

        // Fetch other user reports submitted during this report's active lifetime [T, T + 60]
        $concurrentReports = FlagReport::where('beach_id', $beach->id)
            ->whereBetween('reported_at', [$windowStart, $windowEnd])
            ->where('status', '!=', 'cancelled')
            ->get();

        $distinctUsersCount = $concurrentReports->pluck('user_id')->unique()->count();

        // Rule: Penalization only occurs if there are at least 3 distinct users in the window
        if ($distinctUsersCount >= 3) {
            $votes = ['green' => 0, 'yellow' => 0, 'red' => 0];
            foreach ($concurrentReports as $r) {
                $votes[$r->flag] += $r->vote_weight;
            }

            $totalWeight = array_sum($votes);
            $opposingWeight = $totalWeight - $votes[$report->flag];

            // Penalize if 75% or more of the weighted votes are contrary
            $opposingPercentage = ($opposingWeight / max(1, $totalWeight)) * 100;

            if ($opposingPercentage >= 75.0) {
                // Penalized!
                $report->status = 'rejected';
                $report->resolved_at = now();
                $report->save();

                $this->scoreManager->penalizeReport($report);
                return;
            }
        }

        // Accepted (either consensus agreed, or there weren't enough votes/majority to penalize)
        $report->status = 'confirmed';
        $report->resolved_at = now();
        $report->save();

        $this->scoreManager->addReportPoints($report);
    }
}
