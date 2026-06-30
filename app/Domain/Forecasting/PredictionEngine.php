<?php

namespace App\Domain\Forecasting;

use App\Models\Beach;
use App\Models\FlagPrediction;
use App\Models\OceanForecast;
use App\Models\WeatherForecast;
use App\Models\WaterQualitySnapshot;
use App\Models\OfficialAlert;

class PredictionEngine
{
    private const MAX_DATA_AGE_HOURS = 24;
    private const CONFIDENCE_FRESH_HOURS = 4;
    private const CONFIDENCE_DECAY_PER_HOUR = 6;
    private const CONFIDENCE_MIN = 20;

    // Wave fuzzy membership thresholds (effective height with steepness baked in)
    private const WAVE_GREEN_MAX = 0.7;
    private const WAVE_GREEN_FADE_END = 1.4;
    private const WAVE_YELLOW_START = 0.7;
    private const WAVE_YELLOW_PEAK_START = 1.1;
    private const WAVE_YELLOW_PEAK_END = 2.0;
    private const WAVE_YELLOW_FADE_END = 2.5;
    private const WAVE_RED_START = 1.8;
    private const WAVE_RED_FADE_END = 2.5;

    // Wind fuzzy membership thresholds (effective knots, onshore-weighted)
    private const WIND_GREEN_MAX = 12.0;
    private const WIND_GREEN_FADE_END = 20.0;
    private const WIND_YELLOW_START = 12.0;
    private const WIND_YELLOW_PEAK_START = 16.0;
    private const WIND_YELLOW_PEAK_END = 24.0;
    private const WIND_YELLOW_FADE_END = 28.0;
    private const WIND_RED_START = 22.0;
    private const WIND_RED_FADE_END = 28.0;

    // Wave steepness amplifier thresholds
    private const STEEP_PERIOD_THRESHOLD = 8.0;
    private const STEEP_HEIGHT_THRESHOLD = 0.8;
    private const MODERATE_PERIOD_THRESHOLD = 10.0;
    private const MODERATE_HEIGHT_THRESHOLD = 1.0;
    private const LONG_SWELL_PERIOD_THRESHOLD = 14.0;

    // Direction multipliers
    private const WAVE_DIR_MIN_MULTIPLIER = 0.25;
    private const WAVE_DIR_VARIABLE_MULTIPLIER = 0.75;
    private const WIND_DIR_MIN_MULTIPLIER = 0.5;
    private const WIND_DIR_VARIABLE_MULTIPLIER = 0.7;

    // Slope factors
    private const SLOPE_FACTOR_GENTLE = 1.15;
    private const SLOPE_FACTOR_MEDIUM = 1.0;
    private const SLOPE_FACTOR_STEEP = 0.90;

    // Shelter modifiers
    private const JETTY_SHELTER_MULTIPLIER = 1.6;
    private const BAY_SHELTER_MULTIPLIER = 1.35;
    private const SHELTER_MAX = 4.0;

    // Beach type modifiers
    private const ESTUARINE_WAVE_REDUCTION = 0.4;

    // Environmental modifiers
    private const POOR_QUALITY_RED = 1.0;
    private const SUFFICIENT_QUALITY_YELLOW_FLOOR = 0.7;
    private const SUFFICIENT_QUALITY_GREEN_CEILING = 0.2;
    private const TIDE_LOW_THRESHOLD = 1.0;
    private const TIDE_MODIFIER_WEIGHT = 0.4;
    private const ESTUARY_CURRENT_BASE = 0.45;
    private const ESTUARY_CURRENT_HIGH_RISK_BONUS = 0.15;
    private const ESTUARY_CURRENT_HOURS = 4.0;

    // Default fallback
    private const DEFAULT_WAVE_PERIOD = 8.0;
    private const DEFAULT_COAST_ORIENTATION = 'W';
    private const DEFAULT_WAVE_DIRECTION = 'W';
    private const DEFAULT_WIND_DIRECTION = 'N';

    public function calculate(Beach $beach): FlagPrediction
    {
        $profile  = $beach->predictionProfile ?: $beach->predictionProfile()->create();
        $features = $beach->features ?: $beach->features()->create();

        $ocean   = OceanForecast::where('beach_id', $beach->id)->orderBy('forecasted_at', 'desc')->first();
        $weather = WeatherForecast::where('beach_id', $beach->id)->orderBy('forecasted_at', 'desc')->first();
        $quality = WaterQualitySnapshot::where('beach_id', $beach->id)->orderBy('sampled_at', 'desc')->orderBy('id', 'desc')->first();

        $alert = OfficialAlert::where('beach_id', $beach->id)
            ->orWhereNull('beach_id')
            ->where('started_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('ended_at')->orWhere('ended_at', '>=', now());
            })->first();

        // No data → gray
        if (!$ocean || !$weather) {
            return $this->grayPrediction($beach, $profile);
        }

        $oceanAge   = abs(now()->diffInHours($ocean->created_at));
        $weatherAge = abs(now()->diffInHours($weather->created_at));

        if ($oceanAge > self::MAX_DATA_AGE_HOURS || $weatherAge > self::MAX_DATA_AGE_HOURS) {
            return $this->grayPrediction($beach, $profile);
        }

        if (($beach->type === 'oceanic' && $ocean->wave_height_max === null) || $weather->wind_speed === null) {
            return $this->grayPrediction($beach, $profile);
        }

        // Directional alignment
        $coastAngle = $this->getCompassAngle($features->coast_orientation ?: self::DEFAULT_COAST_ORIENTATION);
        $waveAngle  = $this->getCompassAngle($ocean->wave_direction ?: self::DEFAULT_WAVE_DIRECTION);
        $windAngle  = $this->getCompassAngle($weather->wind_direction ?: self::DEFAULT_WIND_DIRECTION);

        $waveAngleDiff     = $this->getAngleDifference($coastAngle, $waveAngle);
        $waveDirMultiplier = self::WAVE_DIR_MIN_MULTIPLIER + self::WAVE_DIR_VARIABLE_MULTIPLIER * (1.0 - ($waveAngleDiff / 180.0));

        $windAngleDiff     = $this->getAngleDifference($coastAngle, $windAngle);
        $windDirMultiplier = self::WIND_DIR_MIN_MULTIPLIER + self::WIND_DIR_VARIABLE_MULTIPLIER * (1.0 - ($windAngleDiff / 180.0));

        // Wave steepness amplifier
        $wavePeriod    = (float) ($ocean->wave_period_max ?: $ocean->wave_period_min ?: self::DEFAULT_WAVE_PERIOD);
        $waveHeightRaw = (float) ($ocean->wave_height_max ?: $ocean->wave_height_min ?: 0.0);

        $steepnessAmplifier = $this->computeSteepnessAmplifier($wavePeriod, $waveHeightRaw);

        // Beach morphology factors
        $slopeFactor = match($features->slope ?: 'medium') {
            'gentle' => self::SLOPE_FACTOR_GENTLE,
            'medium' => self::SLOPE_FACTOR_MEDIUM,
            'steep'  => self::SLOPE_FACTOR_STEEP,
            default  => self::SLOPE_FACTOR_MEDIUM,
        };

        $exposureFactor   = (float) ($profile->exposure_factor ?? 1.0);
        $shelterFactor    = (float) ($profile->shelter_factor ?? 1.0);
        $waveHeightWeight = (float) ($profile->wave_height_weight ?? 1.0);

        if ($features->has_jetties) {
            $shelterFactor = min(self::SHELTER_MAX, $shelterFactor * self::JETTY_SHELTER_MULTIPLIER);
        }
        if ($features->has_bays) {
            $shelterFactor = min(self::SHELTER_MAX, $shelterFactor * self::BAY_SHELTER_MULTIPLIER);
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
            $effectiveHeight *= self::ESTUARINE_WAVE_REDUCTION;
        }

        // Effective wind
        $windSpeed          = (float) $weather->wind_speed;
        $windWeight         = (float) ($profile->wind_weight ?? 1.0);
        $effectiveWindSpeed = $windSpeed * $windDirMultiplier * $windWeight;

        // Fuzzy membership: wave
        $P_wave_green  = $this->fuzzyTriangular($effectiveHeight, 0, self::WAVE_GREEN_MAX, self::WAVE_GREEN_FADE_END);
        $P_wave_yellow = $this->fuzzyTrapezoidal($effectiveHeight, self::WAVE_YELLOW_START, self::WAVE_YELLOW_PEAK_START, self::WAVE_YELLOW_PEAK_END, self::WAVE_YELLOW_FADE_END);
        $P_wave_red    = $this->fuzzyRamp($effectiveHeight, self::WAVE_RED_START, self::WAVE_RED_FADE_END);

        // Fuzzy membership: wind
        $P_wind_green  = $this->fuzzyTriangular($effectiveWindSpeed, 0, self::WIND_GREEN_MAX, self::WIND_GREEN_FADE_END);
        $P_wind_yellow = $this->fuzzyTrapezoidal($effectiveWindSpeed, self::WIND_YELLOW_START, self::WIND_YELLOW_PEAK_START, self::WIND_YELLOW_PEAK_END, self::WIND_YELLOW_FADE_END);
        $P_wind_red    = $this->fuzzyRamp($effectiveWindSpeed, self::WIND_RED_START, self::WIND_RED_FADE_END);

        // Combine wave + wind
        $rawGreen  = min($P_wave_green, $P_wind_green);
        $rawRed    = max($P_wave_red, $P_wind_red);
        $rawYellow = max(0.0, 1.0 - $rawGreen - $rawRed);

        // Environmental modifiers
        $result = $this->applyEnvironmentalModifiers($beach, $profile, $quality, $weather, $rawGreen, $rawYellow, $rawRed);

        $rawGreen  = $result['green'];
        $rawYellow = $result['yellow'];
        $rawRed    = $result['red'];

        // Normalize
        $sum = $rawGreen + $rawYellow + $rawRed;
        if ($sum > 0) {
            $green  = (int) round(($rawGreen  / $sum) * 100);
            $yellow = (int) round(($rawYellow / $sum) * 100);
            $red    = 100 - $green - $yellow;
        } else {
            $green = 0; $yellow = 0; $red = 100;
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
            $green = 0; $yellow = 0; $red = 100;
        } elseif ($alert && $alert->type === 'warning' && $selectedFlag === 'green') {
            $selectedFlag = 'yellow';
            $green = 10; $yellow = 90; $red = 0;
        }

        // Confidence
        $maxAge     = max($oceanAge, $weatherAge);
        $confidence = 100;
        if ($maxAge > self::CONFIDENCE_FRESH_HOURS) {
            $confidence = max(self::CONFIDENCE_MIN, 100 - ($maxAge * self::CONFIDENCE_DECAY_PER_HOUR));
        }

        return new FlagPrediction([
            'beach_id'           => $beach->id,
            'green_probability'  => $green,
            'yellow_probability' => $yellow,
            'red_probability'    => $red,
            'selected_flag'      => $selectedFlag,
            'confidence'         => (int) $confidence,
            'algorithm_version'  => '2.0',
            'calculated_at'      => now(),
        ]);
    }

    private function computeSteepnessAmplifier(float $wavePeriod, float $waveHeightRaw): float
    {
        $amplifier = 1.0;
        if ($wavePeriod < self::STEEP_PERIOD_THRESHOLD && $waveHeightRaw > self::STEEP_HEIGHT_THRESHOLD) {
            $amplifier = 1.0 + min(0.35, (self::STEEP_PERIOD_THRESHOLD - $wavePeriod) * 0.07);
        } elseif ($wavePeriod < self::MODERATE_PERIOD_THRESHOLD && $waveHeightRaw > self::MODERATE_HEIGHT_THRESHOLD) {
            $amplifier = 1.0 + min(0.15, (self::MODERATE_PERIOD_THRESHOLD - $wavePeriod) * 0.03);
        } elseif ($wavePeriod >= self::LONG_SWELL_PERIOD_THRESHOLD) {
            $amplifier = max(0.85, 1.0 - ($wavePeriod - self::LONG_SWELL_PERIOD_THRESHOLD) * 0.025);
        }
        return $amplifier;
    }

    private function applyEnvironmentalModifiers($beach, $profile, $quality, $weather, float $rawGreen, float $rawYellow, float $rawRed): array
    {
        if ($quality && strtolower($quality->quality_class) === 'poor') {
            return ['green' => 0.0, 'yellow' => 0.0, 'red' => self::POOR_QUALITY_RED];
        }

        if ($weather->jellyfish_risk === 'Alto') {
            $rawRed   = max($rawRed, 0.8);
            $rawGreen = 0.0;
        } elseif ($weather->jellyfish_risk === 'Moderado') {
            $rawYellow = max($rawYellow, 0.7);
            $rawGreen  = min($rawGreen, 0.1);
        }

        if ($quality && strtolower($quality->quality_class) === 'sufficient') {
            $rawYellow = max($rawYellow, self::SUFFICIENT_QUALITY_YELLOW_FLOOR);
            $rawGreen  = min($rawGreen, self::SUFFICIENT_QUALITY_GREEN_CEILING);
        }

        // Tide modifier
        $tideWeight = (float) ($profile->tide_weight ?? 1.0);
        $nextTide   = \App\Models\TideForecast::where('tide_station_id', $beach->tide_station_id)
            ->where('tide_time', '>=', now())
            ->orderBy('tide_time', 'asc')
            ->first();

        if ($nextTide && $tideWeight > 0.0) {
            if ($nextTide->tide_type === 'low' && $nextTide->tide_height < self::TIDE_LOW_THRESHOLD) {
                $rawYellow = max($rawYellow, self::TIDE_MODIFIER_WEIGHT * $tideWeight);
                $rawGreen  = min($rawGreen, 1.0 - (self::TIDE_MODIFIER_WEIGHT * $tideWeight));
            }

            $currentRiskFactor  = (float) ($profile->current_risk_factor ?? 1.0);
            $isEstuarineOrRiver = ($beach->type === 'estuarine') || ($beach->features && $beach->features->river_influence);

            if ($isEstuarineOrRiver && $currentRiskFactor > 0.0 && $nextTide->tide_type === 'low') {
                $hoursToLowTide  = now()->diffInMinutes($nextTide->tide_time) / 60.0;
                if ($hoursToLowTide < self::ESTUARY_CURRENT_HOURS) {
                    $currentModifier = self::ESTUARY_CURRENT_BASE * $currentRiskFactor;
                    if ($beach->features && $beach->features->current_risk === 'high') {
                        $currentModifier += self::ESTUARY_CURRENT_HIGH_RISK_BONUS;
                    }
                    $rawYellow = max($rawYellow, $currentModifier);
                    $rawGreen  = min($rawGreen, 1.0 - $currentModifier);
                }
            }
        }

        $rawYellow = max($rawYellow, 1.0 - $rawGreen - $rawRed);

        return ['green' => $rawGreen, 'yellow' => $rawYellow, 'red' => $rawRed];
    }

    /** Fuzzy triangular membership: 1 at ≤x1, linear to 0 at x2 */
    private function fuzzyTriangular(float $value, float $x0, float $x1, float $x2): float
    {
        if ($value <= $x1) return 1.0;
        if ($value >= $x2) return 0.0;
        return ($x2 - $value) / ($x2 - $x1);
    }

    /** Fuzzy trapezoidal membership: 0 to 1 ramp up, plateau, 1 to 0 ramp down */
    private function fuzzyTrapezoidal(float $value, float $a, float $b, float $c, float $d): float
    {
        if ($value >= $b && $value <= $c) return 1.0;
        if ($value > $a && $value < $b) return ($value - $a) / ($b - $a);
        if ($value > $c && $value < $d) return ($d - $value) / ($d - $c);
        return 0.0;
    }

    /** Fuzzy ramp: 0 at ≤x0, linear to 1 at ≥x1 */
    private function fuzzyRamp(float $value, float $x0, float $x1): float
    {
        if ($value <= $x0) return 0.0;
        if ($value >= $x1) return 1.0;
        return ($value - $x0) / ($x1 - $x0);
    }

    private function grayPrediction(Beach $beach, $profile): FlagPrediction
    {
        return new FlagPrediction([
            'beach_id'           => $beach->id,
            'green_probability'  => 0,
            'yellow_probability' => 0,
            'red_probability'    => 0,
            'selected_flag'      => 'gray',
            'confidence'         => 0,
            'algorithm_version'  => $profile->algorithm_version ?: '2.0',
            'calculated_at'      => now(),
        ]);
    }

    private function getCompassAngle(string $dir): int
    {
        $dir     = strtoupper(trim($dir));
        $mapping = [
            'N'   => 0,   'NNE' => 22,  'NE'  => 45,  'ENE' => 67,
            'E'   => 90,  'ESE' => 112, 'SE'  => 135, 'SSE' => 157,
            'S'   => 180, 'SSW' => 202, 'SW'  => 225, 'WSW' => 247,
            'W'   => 270, 'WNW' => 292, 'NW'  => 315, 'NNW' => 337,
        ];
        return $mapping[$dir] ?? 270;
    }

    private function getAngleDifference(int $angle1, int $angle2): int
    {
        $diff = abs($angle1 - $angle2) % 360;
        return $diff > 180 ? 360 - $diff : $diff;
    }
}
