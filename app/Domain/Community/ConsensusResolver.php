<?php

namespace App\Domain\Community;

use App\Models\Beach;
use App\Models\FlagReport;
use App\Models\BeachCurrentStatus;
use App\Models\FlagPrediction;
use App\Models\OfficialAlert;
use App\Domain\Gamification\ScoreManager;
use Illuminate\Support\Facades\DB;

class ConsensusResolver
{
    // Reason thresholds (duplicated from PredictionEngine for self-contained reasons)
    protected ScoreManager $scoreManager;

    public function __construct(?ScoreManager $scoreManager = null)
    {
        $this->scoreManager = $scoreManager ?? new ScoreManager();
    }

    public function resolveCurrentStatus(Beach $beach): BeachCurrentStatus
    {
        $status = BeachCurrentStatus::firstOrNew(['beach_id' => $beach->id]);
        $status->updated_at = now();

        // 1. Official alerts (absolute priority)
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

        // 2. Community consensus (≥ 2 distinct users in the last 60 minutes)
        $oneHourAgo = now()->subMinutes(config('prediction.consensus.report_window_minutes'));
        $activeReports = FlagReport::where('beach_id', $beach->id)
            ->where('reported_at', '>=', $oneHourAgo)
            ->where('status', '!=', 'cancelled')
            ->get();

        $distinctUsersCount = $activeReports->pluck('user_id')->unique()->count();

        if ($distinctUsersCount >= config('prediction.consensus.community_min_users')) {
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
            } elseif (in_array($latestReportFlag, $winners)) {
                $winningFlag = $latestReportFlag;
            } else {
                $winningFlag = in_array('red', $winners) ? 'red'
                    : (in_array('yellow', $winners) ? 'yellow' : 'green');
            }

            $status->fill([
                'source' => 'community',
                'flag' => $winningFlag,
                'confidence' => config('prediction.consensus.community_confidence'),
                'consensus_reports_count' => $activeReports->count(),
                'reason' => 'Confirmado por utilizadores locais (' . $distinctUsersCount . ' votos na última hora).',
            ]);
            $status->save();
            return $status;
        }

        // 3. Fallback to automatic prediction
        $prediction = FlagPrediction::where('beach_id', $beach->id)->orderBy('calculated_at', 'desc')->first();
        if ($prediction && $prediction->calculated_at->isAfter(now()->subHours(config('prediction.consensus.prediction_max_age_hours')))) {
            $reason = $this->buildPredictionReason($beach, $prediction);

            $status->fill([
                'source' => 'prediction',
                'flag' => $prediction->selected_flag,
                'confidence' => $prediction->confidence,
                'consensus_reports_count' => 0,
                'reason' => $reason,
            ]);
        } else {
            $status->fill([
                'source' => 'prediction',
                'flag' => 'gray',
                'confidence' => 0,
                'consensus_reports_count' => 0,
                'reason' => 'Sem informações de previsão ou relatórios disponíveis.',
            ]);
        }

        // 4. Out of season check
        $today = now()->toDateString();
        if ($beach->season_start && $beach->season_end) {
            if ($today < $beach->season_start->toDateString() || $today > $beach->season_end->toDateString()) {
                $status->flag = 'blue_or_neutral';
                $status->reason = 'Fora da época balnear oficial.';
            }
        }

        $status->save();
        return $status;
    }

    private function buildPredictionReason(Beach $beach, FlagPrediction $prediction): string
    {
        if ($prediction->selected_flag === 'gray') {
            return 'Dados insuficientes ou obsoletos para previsão.';
        }

        $ocean   = \App\Models\OceanForecast::where('beach_id', $beach->id)->orderBy('forecasted_at', 'desc')->first();
        $weather = \App\Models\WeatherForecast::where('beach_id', $beach->id)->orderBy('forecasted_at', 'desc')->first();
        $quality = \App\Models\WaterQualitySnapshot::where('beach_id', $beach->id)->orderBy('sampled_at', 'desc')->orderBy('id', 'desc')->first();

        if ($prediction->selected_flag === 'red') {
            if ($quality && strtolower($quality->quality_class) === 'poor') {
                return 'Qualidade da água imprópria para banhos.';
            }
            if ($ocean && $ocean->wave_height_max > config('prediction.consensus.red_wave_height')) {
                $period = $ocean->wave_period_max ?: $ocean->wave_period_min;
                $periodNote = $period && $period < config('prediction.consensus.short_wave_period')
                    ? ' com período curto de ' . round($period, 1) . 's (rebentação intensa)'
                    : '';
                return 'Ondulação muito forte com ondas de ' . $ocean->wave_height_max . 'm' . $periodNote . ' — banhos proibidos.';
            }
            if ($weather && $weather->wind_speed > config('prediction.consensus.red_wind_speed')) {
                return 'Vento extremamente forte de ' . (int)round($weather->wind_speed * 1.852) . ' km/h (Perigo de correntes).';
            }
            return 'Condições marítimas perigosas. Entrada na água proibida.';
        }

        if ($prediction->selected_flag === 'yellow') {
            $nextTide = \App\Models\TideForecast::where('tide_station_id', $beach->tide_station_id)
                ->where('tide_time', '>=', now())
                ->orderBy('tide_time', 'asc')
                ->first();

            if ($quality && strtolower($quality->quality_class) === 'sufficient') {
                return 'Qualidade da água apenas aceitável para banhos.';
            }
            if ($nextTide && $nextTide->tide_type === 'low'
                && ($beach->type === 'estuarine' || ($beach->features && $beach->features->river_influence))
                && now()->diffInMinutes($nextTide->tide_time) / 60.0 < config('prediction.consensus.estuary_current_hours')) {
                $hours = round(now()->diffInMinutes($nextTide->tide_time) / 60.0, 1);
                return 'Corrente de vazante forte no estuário (' . $hours . 'h para a maré baixa).';
            }
            if ($ocean && $ocean->wave_height_max > config('prediction.consensus.yellow_wave_height')) {
                return 'Ondulação moderada com ondas de até ' . $ocean->wave_height_max . 'm (Recomenda-se precaução).';
            }
            if ($weather && $weather->wind_speed > config('prediction.consensus.yellow_wind_speed')) {
                return 'Vento moderado a forte de ' . (int)round($weather->wind_speed * 1.852) . ' km/h.';
            }
            if ($nextTide && $nextTide->tide_type === 'low') {
                return 'Maré baixa (' . $nextTide->tide_height . 'm) com risco acrescido de correntes e rochas expostas.';
            }
            if ($beach->features && $beach->features->current_risk === 'high') {
                return 'Risco elevado de correntes permanentes nesta praia.';
            }
            return 'Condições marítimas requerem atenção reforçada.';
        }

        // Green
        if ($ocean && $ocean->wave_height_max < 0.6) {
            return 'Mar calmo (' . $ocean->wave_height_max . 'm) e vento fraco.';
        }
        return 'Condições favoráveis de vento, ondulação e qualidade da água.';
    }

    public function resolveReport(FlagReport $report): void
    {
        if ($report->status !== 'pending') {
            return;
        }

        $beach = $report->beach;
        $windowStart = $report->reported_at;
        $windowEnd = $report->reported_at->copy()->addMinutes(config('prediction.consensus.report_window_minutes'));

        $concurrentReports = FlagReport::where('beach_id', $beach->id)
            ->whereBetween('reported_at', [$windowStart, $windowEnd])
            ->where('status', '!=', 'cancelled')
            ->get();

        $distinctUsersCount = $concurrentReports->pluck('user_id')->unique()->count();

        if ($distinctUsersCount >= config('prediction.consensus.penalization_min_users')) {
            $votes = ['green' => 0, 'yellow' => 0, 'red' => 0];
            foreach ($concurrentReports as $r) {
                $votes[$r->flag] += $r->vote_weight;
            }

            $totalWeight = array_sum($votes);
            $opposingWeight = $totalWeight - $votes[$report->flag];
            $opposingPercentage = ($opposingWeight / max(1, $totalWeight)) * 100;

            if ($opposingPercentage >= config('prediction.consensus.penalization_threshold_percent')) {
                $report->status = 'rejected';
                $report->resolved_at = now();
                $report->save();

                $this->scoreManager->penalizeReport($report);
                return;
            }
        }

        $report->status = 'confirmed';
        $report->resolved_at = now();
        $report->save();

        $this->scoreManager->addReportPoints($report);
    }
}
