<?php

return [
    'publisher_id' => env('ADSENSE_PUBLISHER_ID', ''),

    'publisher_src' => 'ca-pub-'.env('ADSENSE_PUBLISHER_ID', ''),

    'auto_ads_enabled' => env('ADSENSE_AUTO_ADS_ENABLED', true),

    'slots' => [
        // Home page — ad every 4 beaches in the list
        'home_between_cards' => env('ADSENSE_SLOT_HOME_BETWEEN', ''),
        'home_bottom' => env('ADSENSE_SLOT_HOME_BOTTOM', ''),

        // Beach detail — one prominent ad before the map
        'beach_detail_bottom' => env('ADSENSE_SLOT_BEACH_BOTTOM', ''),

        // Rankings
        'rankings_bottom' => env('ADSENSE_SLOT_RANKINGS_BOTTOM', ''),

        // Sticky mobile footer ad
        'sticky_bottom' => env('ADSENSE_SLOT_STICKY', ''),

        // About & Contact
        'about_bottom' => env('ADSENSE_SLOT_ABOUT', ''),
        'contact_bottom' => env('ADSENSE_SLOT_CONTACT', ''),
    ],
];
