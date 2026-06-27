<?php

namespace App\Domain\Forecasting;

use App\Models\Beach;
use App\Models\FlagPrediction;
use App\Models\OceanForecast;
use App\Models\WeatherForecast;
use App\Models\WaterQualitySnapshot;
use App\Models\OfficialAlert;
use Carbon\Carbon;

class PredictionEngine
{
    /**
     * Compute flag probabilities and confidence level for a given beach.
     */
    public function calculate(Beach $beach): FlagPrediction
    {
        $profile = $beach->predictionProfile ?: $beach->predictionProfile()->create();
        $features = $beach->features ?: $beach->features()->create();

        // 1. Base probabilities
        $green = 50;
        $yellow = 30;
        $red = 20;
        $confidence = 100;

        // Fetch latest forecasts and alerts
        $ocean = OceanForecast::where('beach_id', $beach->id)->orderBy('forecasted_at', 'desc')->first();
        $weather = WeatherForecast::where('beach_id', $beach->id)->orderBy('forecasted_at', 'desc')->first();
        $quality = WaterQualitySnapshot::where('beach_id', $beach->id)->orderBy('sampled_at', 'desc')->first();
        
        $alert = OfficialAlert::where('beach_id', $beach->id)
            ->orWhereNull('beach_id') // regional or national warnings
            ->where('started_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('ended_at')->orWhere('ended_at', '>=', now());
            })->first();

        // Wave exposure multipliers
        $waveHeight = $ocean ? (float) $ocean->wave_height_max : 0.5; // default calm
        $exposureFactor = (float) ($profile->exposure_factor ?? 1.0);
        $shelterFactor = (float) ($profile->shelter_factor ?? 1.0);

        // Effective wave height at beach based on exposure & shelter factors
        $effectiveHeight = $waveHeight * ($exposureFactor / max(0.1, $shelterFactor));

        // Wind calculations
        $windSpeed = $weather ? (float) $weather->wind_speed : 8.0; // knots
        $windDir = $weather ? $weather->wind_direction : 'N';
        $coastDir = $features->coast_orientation ?: 'W';

        $isOnshore = $this->isWindOnshore($coastDir, $windDir);
        $effectiveWindSpeed = $windSpeed * ($isOnshore ? 1.4 : 0.7);

        // Lifeguard Safety Checklist criteria:
        // 1. RED FLAG (Bathing Forbidden):
        if ($alert && in_array($alert->type, ['interdiction', 'restriction'])) {
            $selectedFlag = 'red';
            $green = 0; $yellow = 0; $red = 100;
        } elseif ($quality && strtolower($quality->quality_class) === 'poor') {
            $selectedFlag = 'red';
            $green = 0; $yellow = 0; $red = 100;
        } elseif ($effectiveHeight > 1.6) {
            $selectedFlag = 'red';
            $green = 0; $yellow = 10; $red = 90;
        } elseif ($effectiveWindSpeed > 22.0) {
            $selectedFlag = 'red';
            $green = 0; $yellow = 15; $red = 85;
        } elseif ($weather && $weather->jellyfish_risk === 'Alto') {
            $selectedFlag = 'red';
            $green = 0; $yellow = 20; $red = 80;
        }
        // 2. YELLOW FLAG (Caution, no swimming):
        elseif ($effectiveHeight > 0.8) {
            $selectedFlag = 'yellow';
            $green = 10; $yellow = 80; $red = 10;
        } elseif ($effectiveWindSpeed > 13.0) {
            $selectedFlag = 'yellow';
            $green = 15; $yellow = 75; $red = 10;
        } elseif ($quality && strtolower($quality->quality_class) === 'sufficient') {
            $selectedFlag = 'yellow';
            $green = 20; $yellow = 70; $red = 10;
        } elseif ($weather && $weather->jellyfish_risk === 'Moderado') {
            $selectedFlag = 'yellow';
            $green = 25; $yellow = 65; $red = 10;
        } elseif ($features->current_risk === 'high') {
            $selectedFlag = 'yellow';
            $green = 30; $yellow = 60; $red = 10;
        } elseif ($alert && $alert->type === 'warning') {
            $selectedFlag = 'yellow';
            $green = 30; $yellow = 60; $red = 10;
        }
        // 3. GREEN FLAG (Safe for bathing):
        else {
            $selectedFlag = 'green';
            $green = 95; $yellow = 5; $red = 0;
        }

        // Compute confidence based on data freshness
        $confidence = 100;
        if (!$ocean || !$weather) {
            $confidence = 50; // no external data
        } else {
            $oceanAge = now()->diffInHours($ocean->created_at);
            $weatherAge = now()->diffInHours($weather->created_at);
            $maxAge = max($oceanAge, $weatherAge);
            if ($maxAge > 4) {
                $confidence = max(20, 100 - ($maxAge * 6));
            }
        }

        return new FlagPrediction([
            'beach_id' => $beach->id,
            'green_probability' => $green,
            'yellow_probability' => $yellow,
            'red_probability' => $red,
            'selected_flag' => $selectedFlag,
            'confidence' => (int) $confidence,
            'algorithm_version' => $profile->algorithm_version ?: '1.0',
            'calculated_at' => now(),
        ]);
    }

    /**
     * Check if the wind blows towards the shoreline (onshore).
     */
    private function isWindOnshore(string $coastDir, string $windDir): bool
    {
        $coastDir = strtoupper(trim($coastDir));
        $windDir = strtoupper(trim($windDir));

        if ($coastDir === $windDir) {
            return false; // Blowing offshore (e.g. Coast faces West, Wind is West, blows from land to sea)
        }

        // Direction mapping for onshore directions (facing coastal target)
        $onshoreMapping = [
            'N' => ['S', 'SW', 'SE'],
            'S' => ['N', 'NW', 'NE'],
            'E' => ['W', 'NW', 'SW'],
            'W' => ['E', 'NE', 'SE'],
            'NW' => ['SE', 'E', 'S'],
            'NE' => ['SW', 'W', 'S'],
            'SW' => ['NE', 'E', 'N'],
            'SE' => ['NW', 'W', 'N']
        ];

        return in_array($windDir, $onshoreMapping[$coastDir] ?? []);
    }
}
