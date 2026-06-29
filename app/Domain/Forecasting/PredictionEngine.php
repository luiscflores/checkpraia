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

        // Fetch latest forecasts and alerts
        $ocean = OceanForecast::where('beach_id', $beach->id)->orderBy('forecasted_at', 'desc')->first();
        $weather = WeatherForecast::where('beach_id', $beach->id)->orderBy('forecasted_at', 'desc')->first();
        $quality = WaterQualitySnapshot::where('beach_id', $beach->id)->orderBy('sampled_at', 'desc')->orderBy('id', 'desc')->first();
        
        $alert = OfficialAlert::where('beach_id', $beach->id)
            ->orWhereNull('beach_id') // regional or national warnings
            ->where('started_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('ended_at')->orWhere('ended_at', '>=', now());
            })->first();

        // 1. If we have no external forecast data, return gray flag (Indisponível)
        if (!$ocean || !$weather) {
            return new FlagPrediction([
                'beach_id' => $beach->id,
                'green_probability' => 0,
                'yellow_probability' => 0,
                'red_probability' => 0,
                'selected_flag' => 'gray',
                'confidence' => 0,
                'algorithm_version' => $profile->algorithm_version ?: '1.0',
                'calculated_at' => now(),
            ]);
        }

        // Validate freshness: if forecasts are older than 24 hours, do not predict (stale data)
        $oceanAge = abs(now()->diffInHours($ocean->created_at));
        $weatherAge = abs(now()->diffInHours($weather->created_at));
        if ($oceanAge > 24 || $weatherAge > 24) {
            return new FlagPrediction([
                'beach_id' => $beach->id,
                'green_probability' => 0,
                'yellow_probability' => 0,
                'red_probability' => 0,
                'selected_flag' => 'gray',
                'confidence' => 0,
                'algorithm_version' => $profile->algorithm_version ?: '1.0',
                'calculated_at' => now(),
            ]);
        }

        // Validate essential data: if essential values are null, do not predict
        if (($beach->type === 'oceanic' && $ocean->wave_height_max === null) || $weather->wind_speed === null) {
            return new FlagPrediction([
                'beach_id' => $beach->id,
                'green_probability' => 0,
                'yellow_probability' => 0,
                'red_probability' => 0,
                'selected_flag' => 'gray',
                'confidence' => 0,
                'algorithm_version' => $profile->algorithm_version ?: '1.0',
                'calculated_at' => now(),
            ]);
        }

        // 2. Base probabilities and calculations
        $coastAngle = $this->getCompassAngle($features->coast_orientation ?: 'W');
        $waveAngle = $this->getCompassAngle($ocean->wave_direction ?: 'W');
        $windAngle = $this->getCompassAngle($weather->wind_direction ?: 'N');

        // Wave alignment to coast: 0 deg difference = head-on swell, 180 deg difference = offshore swell
        $waveAngleDiff = $this->getAngleDifference($coastAngle, $waveAngle);
        $waveDirMultiplier = 0.3 + 1.0 * (1.0 - ($waveAngleDiff / 180.0));

        // Wind alignment to coast: 0 deg difference = onshore wind, 180 deg difference = offshore wind
        $windAngleDiff = $this->getAngleDifference($coastAngle, $windAngle);
        $windDirMultiplier = 0.6 + 0.8 * (1.0 - ($windAngleDiff / 180.0));

        // Wave exposure and profile weight multipliers
        $waveHeight = (float) $ocean->wave_height_max;
        $exposureFactor = (float) ($profile->exposure_factor ?? 1.0);
        $shelterFactor = (float) ($profile->shelter_factor ?? 1.0);
        $waveHeightWeight = (float) ($profile->wave_height_weight ?? 1.0);

        // Effective wave height based on exposure, shelter, wave direction alignment, and weight
        $effectiveHeight = $waveHeight * $waveDirMultiplier * ($exposureFactor / max(0.1, $shelterFactor)) * $waveHeightWeight;

        // Estuarine, fluvial, and lagoon beaches are physically sheltered from ocean swell
        if ($beach->type && $beach->type !== 'oceanic') {
            $effectiveHeight = 0.0;
        }

        // Wind calculations and wind weight multiplier
        $windSpeed = (float) $weather->wind_speed;
        $windWeight = (float) ($profile->wind_weight ?? 1.0);
        $effectiveWindSpeed = $windSpeed * $windDirMultiplier * $windWeight;

        // 3. Compute continuous flag probabilities (triangular/trapezoidal membership functions)
        $P_wave_green = 0.0; $P_wave_yellow = 0.0; $P_wave_red = 0.0;
        if ($effectiveHeight <= 0.6) {
            $P_wave_green = 1.0;
        } elseif ($effectiveHeight < 1.4) {
            $P_wave_green = (1.4 - $effectiveHeight) / 0.8;
        }
        if ($effectiveHeight > 0.6 && $effectiveHeight <= 1.0) {
            $P_wave_yellow = ($effectiveHeight - 0.6) / 0.4;
        } elseif ($effectiveHeight > 1.0 && $effectiveHeight < 2.0) {
            $P_wave_yellow = 1.0;
        } elseif ($effectiveHeight >= 2.0 && $effectiveHeight < 2.4) {
            $P_wave_yellow = (2.4 - $effectiveHeight) / 0.4;
        }
        if ($effectiveHeight > 1.6 && $effectiveHeight < 2.4) {
            $P_wave_red = ($effectiveHeight - 1.6) / 0.8;
        } elseif ($effectiveHeight >= 2.4) {
            $P_wave_red = 1.0;
        }

        $P_wind_green = 0.0; $P_wind_yellow = 0.0; $P_wind_red = 0.0;
        if ($effectiveWindSpeed <= 10.0) {
            $P_wind_green = 1.0;
        } elseif ($effectiveWindSpeed < 18.0) {
            $P_wind_green = (18.0 - $effectiveWindSpeed) / 8.0;
        }
        if ($effectiveWindSpeed > 10.0 && $effectiveWindSpeed <= 15.0) {
            $P_wind_yellow = ($effectiveWindSpeed - 10.0) / 5.0;
        } elseif ($effectiveWindSpeed > 15.0 && $effectiveWindSpeed < 22.0) {
            $P_wind_yellow = 1.0;
        } elseif ($effectiveWindSpeed >= 22.0 && $effectiveWindSpeed < 26.0) {
            $P_wind_yellow = (26.0 - $effectiveWindSpeed) / 4.0;
        }
        if ($effectiveWindSpeed > 18.0 && $effectiveWindSpeed < 26.0) {
            $P_wind_red = ($effectiveWindSpeed - 18.0) / 8.0;
        } elseif ($effectiveWindSpeed >= 26.0) {
            $P_wind_red = 1.0;
        }

        // A beach can only be as safe (green) as the worst of the wave and wind conditions
        $rawGreen = min($P_wave_green, $P_wind_green);
        
        // A beach is as dangerous (red) as the worst of the wave and wind conditions
        $rawRed = max($P_wave_red, $P_wind_red);
        
        // Yellow acts as the logical transition between Green and Red
        $rawYellow = max(0.0, 1.0 - $rawGreen - $rawRed);

        // Apply critical environmental modifiers (e.g. jellyfish, water quality)
        if ($quality && strtolower($quality->quality_class) === 'poor') {
            $rawRed = 1.0; $rawGreen = 0.0; $rawYellow = 0.0;
        } else {
            if ($weather->jellyfish_risk === 'Alto') {
                $rawRed = max($rawRed, 0.8);
                $rawGreen = 0.0;
            } elseif ($weather->jellyfish_risk === 'Moderado') {
                $rawYellow = max($rawYellow, 0.7);
                $rawGreen = min($rawGreen, 0.1);
            }

            if ($quality && strtolower($quality->quality_class) === 'sufficient') {
                $rawYellow = max($rawYellow, 0.7);
                $rawGreen = min($rawGreen, 0.2);
            }

            // Re-adjust yellow to absorb transition after modifiers are applied
            $rawYellow = max($rawYellow, 1.0 - $rawGreen - $rawRed);
        }

        // Normalize to 100% total
        $sum = $rawGreen + $rawYellow + $rawRed;
        if ($sum > 0) {
            $green = (int) round(($rawGreen / $sum) * 100);
            $yellow = (int) round(($rawYellow / $sum) * 100);
            $red = 100 - $green - $yellow;
        } else {
            $green = 0; $yellow = 0; $red = 100;
        }

        // Selected flag is the one with the highest probability
        if ($red >= $green && $red >= $yellow) {
            $selectedFlag = 'red';
        } elseif ($yellow >= $green && $yellow >= $red) {
            $selectedFlag = 'yellow';
        } else {
            $selectedFlag = 'green';
        }

        // Override if official alerts are present
        if ($alert && in_array($alert->type, ['interdiction', 'restriction'])) {
            $selectedFlag = 'red';
            $green = 0; $yellow = 0; $red = 100;
        } elseif ($alert && $alert->type === 'warning') {
            if ($selectedFlag === 'green') {
                $selectedFlag = 'yellow';
                $green = 10; $yellow = 90; $red = 0;
            }
        }

        // Compute confidence based on data freshness
        $oceanAge = abs(now()->diffInHours($ocean->created_at));
        $weatherAge = abs(now()->diffInHours($weather->created_at));
        $maxAge = max($oceanAge, $weatherAge);
        $confidence = 100;
        if ($maxAge > 4) {
            $confidence = max(20, 100 - ($maxAge * 6));
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
     * Map a compass direction string (N, NE, etc.) to degree angle.
     */
    private function getCompassAngle(string $dir): int
    {
        $dir = strtoupper(trim($dir));
        $mapping = [
            'N' => 0,
            'NE' => 45,
            'E' => 90,
            'SE' => 135,
            'S' => 180,
            'SW' => 225,
            'W' => 270,
            'NW' => 315
        ];
        return $mapping[$dir] ?? 270;
    }

    /**
     * Calculate shortest difference in degrees between two angles.
     */
    private function getAngleDifference(int $angle1, int $angle2): int
    {
        $diff = abs($angle1 - $angle2) % 360;
        return $diff > 180 ? 360 - $diff : $diff;
    }
}
