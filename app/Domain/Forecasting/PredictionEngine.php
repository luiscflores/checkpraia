<?php

namespace App\Domain\Forecasting;

use App\Models\Beach;
use App\Models\FlagPrediction;
use App\Models\OceanForecast;
use App\Models\OfficialAlert;
use App\Models\TideForecast;
use App\Models\WaterQualitySnapshot;
use App\Models\WeatherForecast;

class PredictionEngine
{
    private function cfg(string $key, mixed $default = null): mixed
    {
        return config("prediction.{$key}", $default);
    }

    /**
     * Calculate flag prediction and return it alongside the fetched forecast data
     * so callers can reuse the data without redundant DB queries.
     *
     * @return array{prediction: FlagPrediction, ocean: ?OceanForecast, weather: ?WeatherForecast, quality: ?WaterQualitySnapshot}
     */
    public function calculateWithPayload(Beach $beach): array
    {
        $profile = $beach->predictionProfile ?: $beach->predictionProfile()->create();
        $features = $beach->features ?: $beach->features()->create();

        $ocean = OceanForecast::where('beach_id', $beach->id)->orderBy('forecasted_at', 'desc')->first();
        $weather = WeatherForecast::where('beach_id', $beach->id)->orderBy('forecasted_at', 'desc')->first();
        $quality = WaterQualitySnapshot::where('beach_id', $beach->id)->orderBy('sampled_at', 'desc')->orderBy('id', 'desc')->first();

        $prediction = $this->calculateWithPayloadInner($beach, $profile, $features, $ocean, $weather, $quality);

        return [
            'prediction' => $prediction,
            'ocean' => $ocean,
            'weather' => $weather,
            'quality' => $quality,
        ];
    }

    public function calculate(Beach $beach): FlagPrediction
    {
        return $this->calculateWithPayload($beach)['prediction'];
    }

    private function calculateWithPayloadInner(Beach $beach, $profile, $features, $ocean, $weather, $quality): FlagPrediction
    {
        $alert = OfficialAlert::where('beach_id', $beach->id)
            ->orWhereNull('beach_id')
            ->where('started_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('ended_at')->orWhere('ended_at', '>=', now());
            })->first();

        // No data → gray
        if (! $ocean || ! $weather) {
            return $this->grayPrediction($beach, $profile);
        }

        $oceanAge = abs(now()->diffInHours($ocean->created_at));
        $weatherAge = abs(now()->diffInHours($weather->created_at));

        if ($oceanAge > $this->cfg('data_age.max_hours') || $weatherAge > $this->cfg('data_age.max_hours')) {
            return $this->grayPrediction($beach, $profile);
        }

        if (($beach->type === 'oceanic' && $ocean->wave_height_max === null) || $weather->wind_speed === null) {
            return $this->grayPrediction($beach, $profile);
        }

        // Directional alignment
        $coastAngle = $this->getCompassAngle($features->coast_orientation ?: $this->cfg('defaults.coast_orientation'));
        $waveAngle = $this->getCompassAngle($ocean->wave_direction ?: $this->cfg('defaults.wave_direction'));
        $windAngle = $this->getCompassAngle($weather->wind_direction ?: $this->cfg('defaults.wind_direction'));

        $waveAngleDiff = $this->getAngleDifference($coastAngle, $waveAngle);
        $waveDirMultiplier = $this->cfg('direction.wave_min_multiplier') + $this->cfg('direction.wave_variable_multiplier') * (1.0 - ($waveAngleDiff / 180.0));

        $windAngleDiff = $this->getAngleDifference($coastAngle, $windAngle);
        $windDirMultiplier = $this->cfg('direction.wind_min_multiplier') + $this->cfg('direction.wind_variable_multiplier') * (1.0 - ($windAngleDiff / 180.0));

        // Wave steepness amplifier
        $wavePeriod = (float) ($ocean->wave_period_max ?: $ocean->wave_period_min ?: $this->cfg('defaults.wave_period'));
        $waveHeightRaw = (float) ($ocean->wave_height_max ?: $ocean->wave_height_min ?: 0.0);

        $steepnessAmplifier = $this->computeSteepnessAmplifier($wavePeriod, $waveHeightRaw);

        // Beach morphology factors
        $slopeFactor = match ($features->slope ?: 'medium') {
            'gentle' => $this->cfg('slope.gentle_factor'),
            'medium' => $this->cfg('slope.medium_factor'),
            'steep' => $this->cfg('slope.steep_factor'),
            default => $this->cfg('slope.medium_factor'),
        };

        $exposureFactor = (float) ($profile->exposure_factor ?? 1.0);
        $shelterFactor = (float) ($profile->shelter_factor ?? 1.0);
        $waveHeightWeight = (float) ($profile->wave_height_weight ?? 1.0);

        if ($features->has_jetties) {
            $shelterFactor = min($this->cfg('shelter.max'), $shelterFactor * $this->cfg('shelter.jetty_multiplier'));
        }
        if ($features->has_bays) {
            $shelterFactor = min($this->cfg('shelter.max'), $shelterFactor * $this->cfg('shelter.bay_multiplier'));
        }

        // Effective wave height
        $effectiveHeight = $waveHeightRaw
            * $waveDirMultiplier
            * $steepnessAmplifier
            * $slopeFactor
            * ($exposureFactor / max(0.1, $shelterFactor))
            * $waveHeightWeight;

        if (in_array($beach->type, ['fluvial', 'lagoon'])) {
            $effectiveHeight = 0.0;
        } elseif ($beach->type === 'estuarine') {
            $effectiveHeight *= $this->cfg('estuarine.wave_reduction');
        }

        // Effective wind
        $windSpeed = (float) $weather->wind_speed;
        $windWeight = (float) ($profile->wind_weight ?? 1.0);
        $effectiveWindSpeed = $windSpeed * $windDirMultiplier * $windWeight;

        // Fuzzy membership: wave
        $P_wave_green = $this->fuzzyTriangular($effectiveHeight, 0, $this->cfg('waves.green_max'), $this->cfg('waves.green_fade_end'));
        $P_wave_yellow = $this->fuzzyTrapezoidal($effectiveHeight, $this->cfg('waves.yellow_start'), $this->cfg('waves.yellow_peak_start'), $this->cfg('waves.yellow_peak_end'), $this->cfg('waves.yellow_fade_end'));
        $P_wave_red = $this->fuzzyRamp($effectiveHeight, $this->cfg('waves.red_start'), $this->cfg('waves.red_fade_end'));

        // Fuzzy membership: wind
        $P_wind_green = $this->fuzzyTriangular($effectiveWindSpeed, 0, $this->cfg('wind.green_max'), $this->cfg('wind.green_fade_end'));
        $P_wind_yellow = $this->fuzzyTrapezoidal($effectiveWindSpeed, $this->cfg('wind.yellow_start'), $this->cfg('wind.yellow_peak_start'), $this->cfg('wind.yellow_peak_end'), $this->cfg('wind.yellow_fade_end'));
        $P_wind_red = $this->fuzzyRamp($effectiveWindSpeed, $this->cfg('wind.red_start'), $this->cfg('wind.red_fade_end'));

        // Combine wave + wind
        $rawGreen = min($P_wave_green, $P_wind_green);
        $rawRed = max($P_wave_red, $P_wind_red);
        $rawYellow = max(0.0, 1.0 - $rawGreen - $rawRed);

        // Ignore quality data if it's too old
        if ($quality && $quality->sampled_at && $quality->sampled_at->diffInDays(now()) > $this->cfg('quality.max_age_days')) {
            $quality = null;
        }

        // Environmental modifiers
        $result = $this->applyEnvironmentalModifiers($beach, $profile, $quality, $weather, $rawGreen, $rawYellow, $rawRed);

        $rawGreen = $result['green'];
        $rawYellow = $result['yellow'];
        $rawRed = $result['red'];

        // Normalize
        $sum = $rawGreen + $rawYellow + $rawRed;
        if ($sum > 0) {
            $green = (int) round(($rawGreen / $sum) * 100);
            $yellow = (int) round(($rawYellow / $sum) * 100);
            $red = 100 - $green - $yellow;
        } else {
            $green = 0;
            $yellow = 0;
            $red = 100;
        }

        // Select flag
        if ($red >= $green && $red >= $yellow) {
            $selectedFlag = 'red';
        } elseif ($yellow >= $green && $yellow >= $red) {
            $selectedFlag = 'yellow';
        } else {
            $selectedFlag = 'green';
        }

        // Alert override
        if ($alert && in_array($alert->type, ['interdiction', 'restriction'])) {
            $selectedFlag = 'red';
            $green = 0;
            $yellow = 0;
            $red = 100;
        } elseif ($alert && $alert->type === 'warning' && $selectedFlag === 'green') {
            $selectedFlag = 'yellow';
            $green = 10;
            $yellow = 90;
            $red = 0;
        }

        // Confidence
        $maxAge = max($oceanAge, $weatherAge);
        $confidence = 100;
        if ($maxAge > $this->cfg('data_age.confidence_fresh_hours')) {
            $confidence = max($this->cfg('data_age.confidence_min'), 100 - ($maxAge * $this->cfg('data_age.confidence_decay_per_hour')));
        }

        return new FlagPrediction([
            'beach_id' => $beach->id,
            'green_probability' => $green,
            'yellow_probability' => $yellow,
            'red_probability' => $red,
            'selected_flag' => $selectedFlag,
            'confidence' => (int) $confidence,
            'algorithm_version' => $this->cfg('defaults.algorithm_version', '2.0'),
            'calculated_at' => now(),
        ]);
    }

    private function computeSteepnessAmplifier(float $wavePeriod, float $waveHeightRaw): float
    {
        $amplifier = 1.0;
        if ($wavePeriod < $this->cfg('wave_steepness.steep_period_threshold') && $waveHeightRaw > $this->cfg('wave_steepness.steep_height_threshold')) {
            $amplifier = 1.0 + min(0.35, ($this->cfg('wave_steepness.steep_period_threshold') - $wavePeriod) * 0.07);
        } elseif ($wavePeriod < $this->cfg('wave_steepness.moderate_period_threshold') && $waveHeightRaw > $this->cfg('wave_steepness.moderate_height_threshold')) {
            $amplifier = 1.0 + min(0.15, ($this->cfg('wave_steepness.moderate_period_threshold') - $wavePeriod) * 0.03);
        } elseif ($wavePeriod >= $this->cfg('wave_steepness.long_swell_period_threshold')) {
            $amplifier = max(0.85, 1.0 - ($wavePeriod - $this->cfg('wave_steepness.long_swell_period_threshold')) * 0.025);
        }

        return $amplifier;
    }

    private function applyEnvironmentalModifiers($beach, $profile, $quality, $weather, float $rawGreen, float $rawYellow, float $rawRed): array
    {
        if ($quality && strtolower($quality->quality_class) === 'poor') {
            return ['green' => 0.0, 'yellow' => 0.0, 'red' => $this->cfg('quality.poor_quality_red')];
        }

        if ($quality && strtolower($quality->quality_class) === 'sufficient') {
            $rawYellow = max($rawYellow, $this->cfg('quality.sufficient_yellow_floor'));
            $rawGreen = min($rawGreen, $this->cfg('quality.sufficient_green_ceiling'));
        }

        // Tide modifier
        $tideWeight = (float) ($profile->tide_weight ?? 1.0);
        $nextTide = TideForecast::where('tide_station_id', $beach->tide_station_id)
            ->where('tide_time', '>=', now())
            ->orderBy('tide_time', 'asc')
            ->first();

        // Spring tide multiplier based on moon phase
        $springFactor = 1.0;
        if ($nextTide && $nextTide->moon_phase !== null) {
            $moon = (float) $nextTide->moon_phase;
            $distToNew = $moon;
            $distToFull = abs($moon - 0.5);
            $nearestSpring = min($distToNew, $distToFull);
            $springEffect = 1.0 - ($nearestSpring / 0.25);
            $springFactor = 1.0 + $this->cfg('tide.spring_amplifier') * max(0, $springEffect);
        }

        if ($nextTide && $tideWeight > 0.0) {
            if ($nextTide->tide_type === 'low' && $nextTide->tide_height < $this->cfg('tide.low_threshold')) {
                $modifier = $this->cfg('tide.modifier_weight') * $tideWeight * $springFactor;
                $rawYellow = max($rawYellow, $modifier);
                $rawGreen = min($rawGreen, 1.0 - $modifier);
            }

            $currentRiskFactor = (float) ($profile->current_risk_factor ?? 1.0);
            $isEstuarineOrRiver = ($beach->type === 'estuarine') || ($beach->features && $beach->features->river_influence);

            if ($isEstuarineOrRiver && $currentRiskFactor > 0.0 && $nextTide->tide_type === 'low') {
                $hoursToLowTide = now()->diffInMinutes($nextTide->tide_time) / 60.0;
                if ($hoursToLowTide < $this->cfg('estuarine.current_hours')) {
                    $currentModifier = $this->cfg('estuarine.current_base') * $currentRiskFactor * $springFactor;
                    if ($beach->features && $beach->features->current_risk === 'high') {
                        $currentModifier += $this->cfg('estuarine.current_high_risk_bonus') * $springFactor;
                    }
                    $rawYellow = max($rawYellow, $currentModifier);
                    $rawGreen = min($rawGreen, 1.0 - $currentModifier);
                }
            }
        }

        $rawYellow = max($rawYellow, 1.0 - $rawGreen - $rawRed);

        return ['green' => $rawGreen, 'yellow' => $rawYellow, 'red' => $rawRed];
    }

    /** Fuzzy triangular membership: 1 at ≤x1, linear to 0 at x2 */
    private function fuzzyTriangular(float $value, float $x0, float $x1, float $x2): float
    {
        if ($value <= $x1) {
            return 1.0;
        }
        if ($value >= $x2) {
            return 0.0;
        }

        return ($x2 - $value) / ($x2 - $x1);
    }

    /** Fuzzy trapezoidal membership: 0 to 1 ramp up, plateau, 1 to 0 ramp down */
    private function fuzzyTrapezoidal(float $value, float $a, float $b, float $c, float $d): float
    {
        if ($value >= $b && $value <= $c) {
            return 1.0;
        }
        if ($value > $a && $value < $b) {
            return ($value - $a) / ($b - $a);
        }
        if ($value > $c && $value < $d) {
            return ($d - $value) / ($d - $c);
        }

        return 0.0;
    }

    /** Fuzzy ramp: 0 at ≤x0, linear to 1 at ≥x1 */
    private function fuzzyRamp(float $value, float $x0, float $x1): float
    {
        if ($value <= $x0) {
            return 0.0;
        }
        if ($value >= $x1) {
            return 1.0;
        }

        return ($value - $x0) / ($x1 - $x0);
    }

    private function grayPrediction(Beach $beach, $profile): FlagPrediction
    {
        return new FlagPrediction([
            'beach_id' => $beach->id,
            'green_probability' => 0,
            'yellow_probability' => 0,
            'red_probability' => 0,
            'selected_flag' => 'gray',
            'confidence' => 0,
            'algorithm_version' => $profile->algorithm_version ?: $this->cfg('defaults.algorithm_version', '2.0'),
            'calculated_at' => now(),
        ]);
    }

    private function getCompassAngle(string $dir): int
    {
        $dir = strtoupper(trim($dir));
        $mapping = [
            'N' => 0,   'NNE' => 22,  'NE' => 45,  'ENE' => 67,
            'E' => 90,  'ESE' => 112, 'SE' => 135, 'SSE' => 157,
            'S' => 180, 'SSW' => 202, 'SW' => 225, 'WSW' => 247,
            'W' => 270, 'WNW' => 292, 'NW' => 315, 'NNW' => 337,
        ];

        return $mapping[$dir] ?? 270;
    }

    private function getAngleDifference(int $angle1, int $angle2): int
    {
        $diff = abs($angle1 - $angle2) % 360;

        return $diff > 180 ? 360 - $diff : $diff;
    }
}
