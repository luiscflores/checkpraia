<?php

return [

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'tripadvisor' => [
        'key' => env('TRIPADVISOR_API_KEY'),
        'url' => env('TRIPADVISOR_API_URL', 'https://api.content.tripadvisor.com/api/v1/location/nearby_search'),
        'language' => env('TRIPADVISOR_LANGUAGE', 'pt'),
        'timeout' => (int) env('TRIPADVISOR_TIMEOUT', 5),
        'max_results' => (int) env('TRIPADVISOR_MAX_RESULTS', 5),
        'fallback_url' => 'https://www.tripadvisor.com',
    ],

    'thefork' => [
        'key' => env('THEFORK_API_KEY'),
        'url' => env('THEFORK_API_URL', 'https://api.thefork.com/v1/restaurants'),
        'timeout' => (int) env('THEFORK_TIMEOUT', 5),
        'max_results' => (int) env('THEFORK_MAX_RESULTS', 5),
        'fallback_url' => 'https://www.thefork.pt',
    ],

    'openmeteo' => [
        'weather_url' => env('OPENMETEO_WEATHER_URL', 'https://api.open-meteo.com/v1/forecast'),
        'marine_url' => env('OPENMETEO_MARINE_URL', 'https://marine-api.open-meteo.com/v1/marine'),
        'timeout' => (int) env('OPENMETEO_TIMEOUT', 6),
        'retry_times' => (int) env('OPENMETEO_RETRY_TIMES', 3),
        'retry_gap' => (int) env('OPENMETEO_RETRY_GAP', 150),
        'timezone' => env('OPENMETEO_TIMEZONE', 'Europe/London'),
        'wind_speed_unit' => env('OPENMETEO_WIND_UNIT', 'kn'),
    ],

    'overpass' => [
        'url' => env('OVERPASS_API_URL', 'https://overpass-api.de/api/interpreter'),
        'timeout' => (int) env('OVERPASS_TIMEOUT', 10),
        'search_radius' => (int) env('OVERPASS_SEARCH_RADIUS', 1500),
        'max_results' => (int) env('OVERPASS_MAX_RESULTS', 5),
        'query_timeout' => (int) env('OVERPASS_QUERY_TIMEOUT', 10),
    ],

    'infoagua' => [
        'wfs_url' => env('INFOAGUA_WFS_URL', 'https://sniambgeoogc.apambiente.pt/getogc/services/SNIAmb/Praias/MapServer/WFSServer'),
        'arcgis_url' => env('INFOAGUA_ARCGIS_URL', 'https://sniambgeoogc.apambiente.pt/getogc/rest/services/Visualizador/snirh_balneares_classificacoes_app/MapServer/0/query'),
        'timeout' => (int) env('INFOAGUA_TIMEOUT', 15),
    ],

    'tides' => [
        'lookback_days' => (int) env('TIDES_LOOKBACK_DAYS', 1),
        'lookahead_days' => (int) env('TIDES_LOOKAHEAD_DAYS', 7),
    ],

    'tide_stations' => [
        'caminha' => ['lat' => 41.87, 'lon' => -8.83, 'range' => 3.8],
        'viana-do-castelo' => ['lat' => 41.70, 'lon' => -8.83, 'range' => 3.7],
        'povoa-de-varzim' => ['lat' => 41.38, 'lon' => -8.76, 'range' => 3.6],
        'vila-do-conde' => ['lat' => 41.35, 'lon' => -8.74, 'range' => 3.6],
        'porto' => ['lat' => 41.14, 'lon' => -8.62, 'range' => 3.5],
        'vila-nova-de-gaia' => ['lat' => 41.13, 'lon' => -8.61, 'range' => 3.5],
        'matosinhos' => ['lat' => 41.18, 'lon' => -8.68, 'range' => 3.5],
        'esposende' => ['lat' => 41.53, 'lon' => -8.78, 'range' => 3.6],
        'ovar' => ['lat' => 40.86, 'lon' => -8.64, 'range' => 3.4],
        'espinho' => ['lat' => 41.00, 'lon' => -8.64, 'range' => 3.4],
        'murtosa' => ['lat' => 40.75, 'lon' => -8.64, 'range' => 3.3],
        'aveiro' => ['lat' => 40.64, 'lon' => -8.75, 'range' => 3.2],
        'ilhavo' => ['lat' => 40.60, 'lon' => -8.67, 'range' => 3.2],
        'vagos' => ['lat' => 40.55, 'lon' => -8.68, 'range' => 3.2],
        'mira' => ['lat' => 40.43, 'lon' => -8.74, 'range' => 3.1],
        'cantanhede' => ['lat' => 40.35, 'lon' => -8.80, 'range' => 3.1],
        'figueira-da-foz' => ['lat' => 40.15, 'lon' => -8.86, 'range' => 3.0],
        'marinha-grande' => ['lat' => 39.92, 'lon' => -8.93, 'range' => 3.0],
        'leiria' => ['lat' => 39.78, 'lon' => -8.98, 'range' => 2.9],
        'nazare' => ['lat' => 39.60, 'lon' => -9.07, 'range' => 2.8],
        'alcobaca' => ['lat' => 39.52, 'lon' => -9.08, 'range' => 2.8],
        'peniche' => ['lat' => 39.36, 'lon' => -9.38, 'range' => 2.7],
        'lourinha' => ['lat' => 39.25, 'lon' => -9.34, 'range' => 2.7],
        'torres-vedras' => ['lat' => 39.10, 'lon' => -9.40, 'range' => 2.6],
        'mafra' => ['lat' => 38.95, 'lon' => -9.38, 'range' => 2.6],
        'sintra' => ['lat' => 38.82, 'lon' => -9.50, 'range' => 2.6],
        'cascais' => ['lat' => 38.70, 'lon' => -9.42, 'range' => 2.5],
        'oeiras' => ['lat' => 38.69, 'lon' => -9.31, 'range' => 2.5],
        'almada' => ['lat' => 38.65, 'lon' => -9.25, 'range' => 2.5],
        'sesimbra' => ['lat' => 38.44, 'lon' => -9.10, 'range' => 2.4],
        'setubal' => ['lat' => 38.52, 'lon' => -8.89, 'range' => 2.5],
        'grandola' => ['lat' => 38.25, 'lon' => -8.82, 'range' => 2.4],
        'sines' => ['lat' => 37.96, 'lon' => -8.87, 'range' => 2.3],
        'odemira' => ['lat' => 37.70, 'lon' => -8.80, 'range' => 2.2],
        'aljezur' => ['lat' => 37.32, 'lon' => -8.80, 'range' => 2.1],
        'vila-do-bispo' => ['lat' => 37.08, 'lon' => -8.88, 'range' => 2.0],
        'silves' => ['lat' => 37.15, 'lon' => -8.44, 'range' => 2.0],
        'lagoa' => ['lat' => 37.12, 'lon' => -8.45, 'range' => 2.0],
        'portimao' => ['lat' => 37.14, 'lon' => -8.54, 'range' => 2.0],
        'lagos' => ['lat' => 37.10, 'lon' => -8.67, 'range' => 2.0],
        'loule' => ['lat' => 37.07, 'lon' => -8.02, 'range' => 2.0],
        'albufeira' => ['lat' => 37.09, 'lon' => -8.25, 'range' => 2.0],
        'faro' => ['lat' => 37.02, 'lon' => -7.93, 'range' => 2.0],
        'olhao' => ['lat' => 37.03, 'lon' => -7.84, 'range' => 2.0],
        'tavira' => ['lat' => 37.12, 'lon' => -7.65, 'range' => 2.0],
        'vila-real-de-santo-antonio' => ['lat' => 37.19, 'lon' => -7.42, 'range' => 2.0],
        'caldas-da-rainha' => ['lat' => 39.40, 'lon' => -9.14, 'range' => 2.7],
        'funchal' => ['lat' => 32.64, 'lon' => -16.91, 'range' => 2.5],
        'machico' => ['lat' => 32.72, 'lon' => -16.77, 'range' => 2.5],
        'porto-moniz' => ['lat' => 32.87, 'lon' => -17.17, 'range' => 2.5],
        'porto-santo' => ['lat' => 33.07, 'lon' => -16.35, 'range' => 2.5],
        'ribeira-grande' => ['lat' => 37.82, 'lon' => -25.52, 'range' => 1.8],
        'povoacao' => ['lat' => 37.75, 'lon' => -25.25, 'range' => 1.8],
        'horta' => ['lat' => 38.53, 'lon' => -28.63, 'range' => 1.5],
    ],
];
