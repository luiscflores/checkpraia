<?php

return [
    'publisher_id' => env('ADSENSE_PUBLISHER_ID', ''),

    'publisher_src' => 'ca-pub-' . env('ADSENSE_PUBLISHER_ID', ''),

    'auto_ads_enabled' => env('ADSENSE_AUTO_ADS_ENABLED', true),

    'slots' => [
        // Home page
        'home_between_cards' => env('ADSENSE_SLOT_HOME_BETWEEN', ''),
        'home_sidebar_top' => env('ADSENSE_SLOT_HOME_SIDEBAR_TOP', ''),
        'home_sidebar_bottom' => env('ADSENSE_SLOT_HOME_SIDEBAR_BOTTOM', ''),
        'home_bottom' => env('ADSENSE_SLOT_HOME_BOTTOM', ''),

        // Beach detail
        'beach_detail_header' => env('ADSENSE_SLOT_BEACH_HEADER', ''),
        'beach_detail_inline' => env('ADSENSE_SLOT_BEACH_INLINE', ''),
        'beach_detail_bottom' => env('ADSENSE_SLOT_BEACH_BOTTOM', ''),

        // Rankings
        'rankings_header' => env('ADSENSE_SLOT_RANKINGS_HEADER', ''),
        'rankings_bottom' => env('ADSENSE_SLOT_RANKINGS_BOTTOM', ''),

        // Footer / sticky
        'sticky_bottom' => env('ADSENSE_SLOT_STICKY', ''),

        // About & Contact
        'about_bottom' => env('ADSENSE_SLOT_ABOUT', ''),
        'contact_bottom' => env('ADSENSE_SLOT_CONTACT', ''),
    ],
];
