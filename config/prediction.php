<?php

return [
    'data_age' => [
        'max_hours' => (int) env('PREDICTION_MAX_DATA_AGE', 24),
        'confidence_fresh_hours' => (int) env('PREDICTION_CONFIDENCE_FRESH', 4),
        'confidence_decay_per_hour' => (int) env('PREDICTION_CONFIDENCE_DECAY', 6),
        'confidence_min' => (int) env('PREDICTION_CONFIDENCE_MIN', 20),
    ],

    'waves' => [
        'green_max' => (float) env('WAVE_GREEN_MAX', 0.7),
        'green_fade_end' => (float) env('WAVE_GREEN_FADE_END', 1.4),
        'yellow_start' => (float) env('WAVE_YELLOW_START', 0.7),
        'yellow_peak_start' => (float) env('WAVE_YELLOW_PEAK_START', 1.1),
        'yellow_peak_end' => (float) env('WAVE_YELLOW_PEAK_END', 2.0),
        'yellow_fade_end' => (float) env('WAVE_YELLOW_FADE_END', 2.5),
        'red_start' => (float) env('WAVE_RED_START', 1.8),
        'red_fade_end' => (float) env('WAVE_RED_FADE_END', 2.5),
    ],

    'wind' => [
        'green_max' => (float) env('WIND_GREEN_MAX', 12.0),
        'green_fade_end' => (float) env('WIND_GREEN_FADE_END', 20.0),
        'yellow_start' => (float) env('WIND_YELLOW_START', 12.0),
        'yellow_peak_start' => (float) env('WIND_YELLOW_PEAK_START', 16.0),
        'yellow_peak_end' => (float) env('WIND_YELLOW_PEAK_END', 24.0),
        'yellow_fade_end' => (float) env('WIND_YELLOW_FADE_END', 28.0),
        'red_start' => (float) env('WIND_RED_START', 22.0),
        'red_fade_end' => (float) env('WIND_RED_FADE_END', 28.0),
    ],

    'wave_steepness' => [
        'steep_period_threshold' => (float) env('STEEP_PERIOD_THRESHOLD', 8.0),
        'steep_height_threshold' => (float) env('STEEP_HEIGHT_THRESHOLD', 0.8),
        'moderate_period_threshold' => (float) env('MODERATE_PERIOD_THRESHOLD', 10.0),
        'moderate_height_threshold' => (float) env('MODERATE_HEIGHT_THRESHOLD', 1.0),
        'long_swell_period_threshold' => (float) env('LONG_SWELL_PERIOD_THRESHOLD', 14.0),
    ],

    'direction' => [
        'wave_min_multiplier' => (float) env('WAVE_DIR_MIN_MULTIPLIER', 0.25),
        'wave_variable_multiplier' => (float) env('WAVE_DIR_VARIABLE_MULTIPLIER', 0.75),
        'wind_min_multiplier' => (float) env('WIND_DIR_MIN_MULTIPLIER', 0.5),
        'wind_variable_multiplier' => (float) env('WIND_DIR_VARIABLE_MULTIPLIER', 0.7),
    ],

    'shelter' => [
        'jetty_multiplier' => (float) env('SHELTER_JETTY_MULTIPLIER', 1.6),
        'bay_multiplier' => (float) env('SHELTER_BAY_MULTIPLIER', 1.35),
        'max' => (float) env('SHELTER_MAX', 4.0),
    ],

    'slope' => [
        'gentle_factor' => (float) env('SLOPE_GENTLE_FACTOR', 1.15),
        'medium_factor' => (float) env('SLOPE_MEDIUM_FACTOR', 1.0),
        'steep_factor' => (float) env('SLOPE_STEEP_FACTOR', 0.90),
    ],

    'estuarine' => [
        'wave_reduction' => (float) env('ESTUARINE_WAVE_REDUCTION', 0.4),
        'current_base' => (float) env('ESTUARINE_CURRENT_BASE', 0.45),
        'current_high_risk_bonus' => (float) env('ESTUARINE_CURRENT_HIGH_RISK_BONUS', 0.15),
        'current_hours' => (float) env('ESTUARINE_CURRENT_HOURS', 4.0),
    ],

    'tide' => [
        'low_threshold' => (float) env('TIDE_LOW_THRESHOLD', 1.0),
        'modifier_weight' => (float) env('TIDE_MODIFIER_WEIGHT', 0.4),
        'spring_amplifier' => (float) env('TIDE_SPRING_AMPLIFIER', 0.25),
    ],

    'quality' => [
        'poor_quality_red' => (float) env('POOR_QUALITY_RED', 1.0),
        'sufficient_yellow_floor' => (float) env('SUFFICIENT_QUALITY_YELLOW_FLOOR', 0.7),
        'sufficient_green_ceiling' => (float) env('SUFFICIENT_QUALITY_GREEN_CEILING', 0.2),
        'max_age_days' => (int) env('QUALITY_MAX_AGE_DAYS', 21),
    ],

    'defaults' => [
        'wave_period' => (float) env('DEFAULT_WAVE_PERIOD', 8.0),
        'coast_orientation' => env('DEFAULT_COAST_ORIENTATION', 'W'),
        'wave_direction' => env('DEFAULT_WAVE_DIRECTION', 'W'),
        'wind_direction' => env('DEFAULT_WIND_DIRECTION', 'N'),
        'algorithm_version' => env('ALGORITHM_VERSION', '2.0'),
    ],

    'consensus' => [
        'report_window_minutes' => (int) env('CONSENSUS_REPORT_WINDOW', 60),
        'community_min_users' => (int) env('CONSENSUS_COMMUNITY_MIN_USERS', 2),
        'community_confidence' => (int) env('CONSENSUS_COMMUNITY_CONFIDENCE', 95),
        'prediction_max_age_hours' => (int) env('CONSENSUS_PREDICTION_MAX_AGE', 24),
        'penalization_min_users' => (int) env('CONSENSUS_PENALIZATION_MIN_USERS', 3),
        'penalization_threshold_percent' => (float) env('CONSENSUS_PENALIZATION_THRESHOLD', 75.0),
        'red_wave_height' => (float) env('CONSENSUS_RED_WAVE_HEIGHT', 2.0),
        'red_wind_speed' => (float) env('CONSENSUS_RED_WIND_SPEED', 22.0),
        'short_wave_period' => (float) env('CONSENSUS_SHORT_WAVE_PERIOD', 8.0),
        'yellow_wave_height' => (float) env('CONSENSUS_YELLOW_WAVE_HEIGHT', 1.2),
        'yellow_wind_speed' => (float) env('CONSENSUS_YELLOW_WIND_SPEED', 14.0),
        'estuary_current_hours' => (float) env('CONSENSUS_ESTUARY_CURRENT_HOURS', 4.0),
    ],
];
