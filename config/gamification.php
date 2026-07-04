<?php

return [
    'points' => [
        'base_report' => 1,
        'first_report_bonus' => 1,
        'penalty' => -2,
        'penalty_floor' => 0,
    ],

    'vote_weight' => [
        'min_accepted' => 50,
        'success_rate' => 0.90,
        'normal' => 1,
        'reinforced' => 2,
    ],

    'referrals' => [
        'per_bonus' => 5,
        'bonus_points' => 10,
    ],

    'report' => [
        'cooldown_minutes' => (int) env('REPORT_COOLDOWN_MINUTES', 60),
        'max_distance_km' => (float) env('REPORT_MAX_DISTANCE_KM', 1.0),
    ],
];
