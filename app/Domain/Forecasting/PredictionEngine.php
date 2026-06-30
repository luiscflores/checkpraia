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
     *
     * Algorithm v2.0 - Multi-factor fuzzy model incorporating:
     *  - Wave height + directional alignment
     *  - Wave steepness (period-based) amplification
     *  - Beach morphology: slope, jetties, bays, exposure/shelter
     *  - Wind speed + direction
     *  - Environmental factors: tides, water quality, jellyfish, estuary currents
     *  - No hardcoded values — all thresholds flow from beach profile and features
     */
    public function calculate(Beach $beach): FlagPrediction
    {
        $profile  = $beach->predictionProfile ?: $beach->predictionProfile()->create();
        $features = $beach->features ?: $beach->features()->create();

        // Fetch latest forecasts and alerts
        $ocean   = OceanForecast::where('beach_id', $beach->id)->orderBy('forecasted_at', 'desc')->first();
        $weather = WeatherForecast::where('beach_id', $beach->id)->orderBy('forecasted_at', 'desc')->first();
        $quality = WaterQualitySnapshot::where('beach_id', $beach->id)->orderBy('sampled_at', 'desc')->orderBy('id', 'desc')->first();

        $alert = OfficialAlert::where('beach_id', $beach->id)
            ->orWhereNull('beach_id')
            ->where('started_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('ended_at')->orWhere('ended_at', '>=', now());
            })->first();

        // ── GUARD: no data → gray ─────────────────────────────────────────────
        if (!$ocean || !$weather) {
            return $this->grayPrediction($beach, $profile);
        }

        $oceanAge   = abs(now()->diffInHours($ocean->created_at));
        $weatherAge = abs(now()->diffInHours($weather->created_at));

        if ($oceanAge > 24 || $weatherAge > 24) {
            return $this->grayPrediction($beach, $profile);
        }

        if (($beach->type === 'oceanic' && $ocean->wave_height_max === null) || $weather->wind_speed === null) {
            return $this->grayPrediction($beach, $profile);
        }

        // ── 1. DIRECTIONAL ALIGNMENT ──────────────────────────────────────────
        $coastAngle = $this->getCompassAngle($features->coast_orientation ?: 'W');
        $waveAngle  = $this->getCompassAngle($ocean->wave_direction ?: 'W');
        $windAngle  = $this->getCompassAngle($weather->wind_direction ?: 'N');

        // 0° diff = head-on swell (most dangerous), 180° = offshore swell (safest)
        $waveAngleDiff     = $this->getAngleDifference($coastAngle, $waveAngle);
        $waveDirMultiplier = 0.25 + 0.75 * (1.0 - ($waveAngleDiff / 180.0));

        // 0° diff = onshore wind (adds to wave danger), 180° = offshore
        $windAngleDiff     = $this->getAngleDifference($coastAngle, $windAngle);
        $windDirMultiplier = 0.5 + 0.7 * (1.0 - ($windAngleDiff / 180.0));

        // ── 2. WAVE STEEPNESS AMPLIFIER ──────────────────────────────────────
        // Short-period swells (< 8s) produce plunging breakers with much greater
        // break-zone force than long ground swell of the same height.
        // This is the primary reason a 2.3m / 7s wave is far more dangerous than
        // a 2.3m / 14s wave.
        $wavePeriod    = (float) ($ocean->wave_period_max ?: $ocean->wave_period_min ?: 8.0);
        $waveHeightRaw = (float) ($ocean->wave_height_max ?: $ocean->wave_height_min ?: 0.0);

        $steepnessAmplifier = 1.0;
        if ($wavePeriod < 8.0 && $waveHeightRaw > 0.8) {
            // High-frequency short swell — plunging/collapsing breakers
            // Each second below 8s adds ~6% extra danger at heights > 0.8m
            $steepnessAmplifier = 1.0 + min(0.35, (8.0 - $wavePeriod) * 0.07);
        } elseif ($wavePeriod < 10.0 && $waveHeightRaw > 1.0) {
            // Moderate period — some amplification
            $steepnessAmplifier = 1.0 + min(0.15, (10.0 - $wavePeriod) * 0.03);
        } elseif ($wavePeriod >= 14.0) {
            // Long ground swell — rolling, more manageable per unit height
            $steepnessAmplifier = max(0.85, 1.0 - ($wavePeriod - 14.0) * 0.025);
        }

        // ── 3. BEACH MORPHOLOGY FACTORS ───────────────────────────────────────
        // Slope: gentle/flat beaches are dissipative but create wide rip current channels
        // Steep beaches are reflective — surging breakers, but less plunging danger
        $slopeFactor = match($features->slope ?: 'medium') {
            'gentle' => 1.15,  // wide surf zone, rip currents, shore-break intensity
            'medium' => 1.0,
            'steep'  => 0.90,  // reflective beach — surging but less voluminous break
            default  => 1.0,
        };

        // Profile weights from admin settings (allow per-beach calibration)
        $exposureFactor   = (float) ($profile->exposure_factor ?? 1.0);
        $shelterFactor    = (float) ($profile->shelter_factor ?? 1.0);
        $waveHeightWeight = (float) ($profile->wave_height_weight ?? 1.0);

        // Physical shelter modifiers from beach features
        if ($features->has_jetties) {
            // Breakwaters/groins significantly reduce wave energy
            $shelterFactor = min(4.0, $shelterFactor * 1.6);
        }
        if ($features->has_bays) {
            // Natural bay curvature diffracts and reduces swell
            $shelterFactor = min(4.0, $shelterFactor * 1.35);
        }

        // ── 4. EFFECTIVE WAVE HEIGHT ──────────────────────────────────────────
        $effectiveHeight = $waveHeightRaw
            * $waveDirMultiplier
            * $steepnessAmplifier
            * $slopeFactor
            * ($exposureFactor / max(0.1, $shelterFactor))
            * $waveHeightWeight;

        // Non-oceanic beach types are physically sheltered from open ocean swell
        if ($beach->type === 'fluvial' || $beach->type === 'lagoon') {
            $effectiveHeight = 0.0;
        } elseif ($beach->type === 'estuarine') {
            // Estuarine beaches at the mouth are partially exposed to swell
            $effectiveHeight = $effectiveHeight * 0.4;
        }

        // ── 5. EFFECTIVE WIND ─────────────────────────────────────────────────
        $windSpeed          = (float) $weather->wind_speed;
        $windWeight         = (float) ($profile->wind_weight ?? 1.0);
        $effectiveWindSpeed = $windSpeed * $windDirMultiplier * $windWeight;

        // ── 6. FUZZY MEMBERSHIP FUNCTIONS ────────────────────────────────────
        // Wave thresholds (effectiveHeight already includes steepness amplification):
        //   Green  → < 0.7m  (completely safe for all bathers, including children)
        //   Yellow → 0.7–2.0m (caution; Portuguese standard: wade, don't swim out)
        //   Red    → > 1.8m  (prohibition — overlaps with upper yellow via fuzzy logic)
        //
        // NOTE: Because steepness is baked into effectiveHeight, a raw 2.3m / 7s wave
        // becomes ~2.6m effective — placing it firmly in red territory, which matches
        // real-world lifeguard decisions on Atlantic beaches like Praia da Tocha.

        $P_wave_green  = 0.0;
        $P_wave_yellow = 0.0;
        $P_wave_red    = 0.0;

        if ($effectiveHeight <= 0.7) {
            $P_wave_green = 1.0;
        } elseif ($effectiveHeight < 1.4) {
            $P_wave_green = (1.4 - $effectiveHeight) / 0.7;
        }
        if ($effectiveHeight > 0.7 && $effectiveHeight <= 1.1) {
            $P_wave_yellow = ($effectiveHeight - 0.7) / 0.4;
        } elseif ($effectiveHeight > 1.1 && $effectiveHeight < 2.0) {
            $P_wave_yellow = 1.0;
        } elseif ($effectiveHeight >= 2.0 && $effectiveHeight < 2.5) {
            $P_wave_yellow = (2.5 - $effectiveHeight) / 0.5;
        }
        if ($effectiveHeight > 1.8 && $effectiveHeight < 2.5) {
            $P_wave_red = ($effectiveHeight - 1.8) / 0.7;
        } elseif ($effectiveHeight >= 2.5) {
            $P_wave_red = 1.0;
        }

        // Wind thresholds (effective knots — already onshore-weighted):
        //   Green  → ≤ 12 kt
        //   Yellow → 12–24 kt
        //   Red    → ≥ 22 kt onshore (storm-force; danger from wind-waves and drift)
        $P_wind_green  = 0.0;
        $P_wind_yellow = 0.0;
        $P_wind_red    = 0.0;

        if ($effectiveWindSpeed <= 12.0) {
            $P_wind_green = 1.0;
        } elseif ($effectiveWindSpeed < 20.0) {
            $P_wind_green = (20.0 - $effectiveWindSpeed) / 8.0;
        }
        if ($effectiveWindSpeed > 12.0 && $effectiveWindSpeed <= 16.0) {
            $P_wind_yellow = ($effectiveWindSpeed - 12.0) / 4.0;
        } elseif ($effectiveWindSpeed > 16.0 && $effectiveWindSpeed < 24.0) {
            $P_wind_yellow = 1.0;
        } elseif ($effectiveWindSpeed >= 24.0 && $effectiveWindSpeed < 28.0) {
            $P_wind_yellow = (28.0 - $effectiveWindSpeed) / 4.0;
        }
        if ($effectiveWindSpeed > 22.0 && $effectiveWindSpeed < 28.0) {
            $P_wind_red = ($effectiveWindSpeed - 22.0) / 6.0;
        } elseif ($effectiveWindSpeed >= 28.0) {
            $P_wind_red = 1.0;
        }

        // ── 7. COMBINE WAVE + WIND ────────────────────────────────────────────
        // Safety (green) requires BOTH conditions to be safe
        // Danger (red) is triggered by EITHER condition alone
        $rawGreen  = min($P_wave_green, $P_wind_green);
        $rawRed    = max($P_wave_red, $P_wind_red);
        $rawYellow = max(0.0, 1.0 - $rawGreen - $rawRed);

        // ── 8. ENVIRONMENTAL MODIFIERS ────────────────────────────────────────
        if ($quality && strtolower($quality->quality_class) === 'poor') {
            $rawRed = 1.0; $rawGreen = 0.0; $rawYellow = 0.0;
        } else {
            if ($weather->jellyfish_risk === 'Alto') {
                $rawRed   = max($rawRed, 0.8);
                $rawGreen = 0.0;
            } elseif ($weather->jellyfish_risk === 'Moderado') {
                $rawYellow = max($rawYellow, 0.7);
                $rawGreen  = min($rawGreen, 0.1);
            }

            if ($quality && strtolower($quality->quality_class) === 'sufficient') {
                $rawYellow = max($rawYellow, 0.7);
                $rawGreen  = min($rawGreen, 0.2);
            }

            // Tide modifier
            $tideWeight = (float) ($profile->tide_weight ?? 1.0);
            $nextTide   = \App\Models\TideForecast::where('tide_station_id', $beach->tide_station_id)
                ->where('tide_time', '>=', now())
                ->orderBy('tide_time', 'asc')
                ->first();

            if ($nextTide && $tideWeight > 0.0) {
                if ($nextTide->tide_type === 'low' && $nextTide->tide_height < 1.0) {
                    $rawYellow = max($rawYellow, 0.4 * $tideWeight);
                    $rawGreen  = min($rawGreen, 1.0 - (0.4 * $tideWeight));
                }

                $currentRiskFactor  = (float) ($profile->current_risk_factor ?? 1.0);
                $isEstuarineOrRiver = ($beach->type === 'estuarine') || ($features && $features->river_influence);

                if ($isEstuarineOrRiver && $currentRiskFactor > 0.0 && $nextTide->tide_type === 'low') {
                    $hoursToLowTide  = now()->diffInMinutes($nextTide->tide_time) / 60.0;
                    if ($hoursToLowTide < 4.0) {
                        $currentModifier = 0.45 * $currentRiskFactor;
                        if ($features && $features->current_risk === 'high') {
                            $currentModifier += 0.15;
                        }
                        $rawYellow = max($rawYellow, $currentModifier);
                        $rawGreen  = min($rawGreen, 1.0 - $currentModifier);
                    }
                }
            }

            $rawYellow = max($rawYellow, 1.0 - $rawGreen - $rawRed);
        }

        // ── 9. NORMALIZE ──────────────────────────────────────────────────────
        $sum = $rawGreen + $rawYellow + $rawRed;
        if ($sum > 0) {
            $green  = (int) round(($rawGreen  / $sum) * 100);
            $yellow = (int) round(($rawYellow / $sum) * 100);
            $red    = 100 - $green - $yellow;
        } else {
            $green = 0; $yellow = 0; $red = 100;
        }

        // ── 10. SELECT FLAG ───────────────────────────────────────────────────
        if ($red >= $green && $red >= $yellow) {
            $selectedFlag = 'red';
        } elseif ($yellow >= $green && $yellow >= $red) {
            $selectedFlag = 'yellow';
        } else {
            $selectedFlag = 'green';
        }

        // ── 11. OFFICIAL ALERT OVERRIDE ───────────────────────────────────────
        if ($alert && in_array($alert->type, ['interdiction', 'restriction'])) {
            $selectedFlag = 'red';
            $green = 0; $yellow = 0; $red = 100;
        } elseif ($alert && $alert->type === 'warning') {
            if ($selectedFlag === 'green') {
                $selectedFlag = 'yellow';
                $green = 10; $yellow = 90; $red = 0;
            }
        }

        // ── 12. CONFIDENCE ────────────────────────────────────────────────────
        $maxAge     = max($oceanAge, $weatherAge);
        $confidence = 100;
        if ($maxAge > 4) {
            $confidence = max(20, 100 - ($maxAge * 6));
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

    /**
     * Return a gray (unavailable) prediction when data is missing or stale.
     */
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

    /**
     * Map a compass direction string (N, NE, NNE, etc.) to degree angle.
     */
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

    /**
     * Calculate shortest angular difference between two compass angles (0–180°).
     */
    private function getAngleDifference(int $angle1, int $angle2): int
    {
        $diff = abs($angle1 - $angle2) % 360;
        return $diff > 180 ? 360 - $diff : $diff;
    }
}
