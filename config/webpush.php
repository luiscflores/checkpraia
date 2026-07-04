<?php

return [
    'vapid' => [
        'subject' => env('VAPID_SUBJECT', 'mailto:info@checkpraia.pt'),
        'public_key' => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
    ],
    'nearby_radius_km' => env('PUSH_NEARBY_RADIUS_KM', 10),
];
