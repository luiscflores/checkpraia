<?php

return [
    'page_title' => ':name - Flag and Sea Conditions',
    'meta_description' => 'Current flag, water temperature, waves, wind and weather forecast for :name in :municipality.',
    'back_to_map' => '← Back to Map',
    'region_badge' => ':region',

    // Flag section
    'flag_title' => 'Current Flag',
    'flag_official' => 'Official coastal data collection.',
    'flag_community' => 'With community confirmation.',
    'flag_predicted' => 'Automatically predicted flag.',
    'flag_off_season' => 'Beach is out of swimming season.',
    'flag_verified_by' => 'Verified by :count users',
    'today_votes_title' => "Today's Confirmations",
    'history_title' => "Today's Evolution",
    'flag_report' => 'Report Flag',

    // Weather
    'weather_title' => 'Current Conditions',
    'weather_air' => 'Air Temperature',
    'weather_water' => 'Water Temperature',
    'weather_waves' => 'Wave Height',
    'weather_wind' => 'Wind Speed & Direction',
    'weather_humidity' => 'Humidity',
    'weather_uv' => 'UV Index',
    'weather_wave_period' => 'Period',
    'weather_wave_direction' => 'Direction',
    'weather_wind_speed' => ':speed km/h',
    'weather_wind_direction' => ':direction',

    // Tide
    'tide_title' => 'Tides',
    'tide_high' => 'High Tide',
    'tide_low' => 'Low Tide',
    'tide_next_high' => 'Next High Tide',
    'tide_next_low' => 'Next Low Tide',
    'tide_today' => 'Today\'s Tides',
    'tide_height' => ':height m',

    // Jellyfish
    'jellyfish_title' => 'Jellyfish Risk',
    'jellyfish_low' => '🟢 Low',
    'jellyfish_moderate' => '🟡 Moderate',
    'jellyfish_high' => '🔴 High',
    'jellyfish_description' => 'Estimate based on water temperature, wind and seasonality.',

    // About section
    'about_title' => 'About This Beach',
    'about_services' => 'Available Services',

    // Moon
    'moon_title' => 'Lunar Calendar',
    'moon_phase' => 'Moon Phase',
    'moon_spring_tide' => 'Spring Tide',
    'moon_neap_tide' => 'Neap Tide',
    'moon_upcoming' => 'Upcoming Phases',

    // Report
    'report_title' => 'Report Flag',
    'report_description' => 'Did you see the flag today? Help the community.',
    'report_green' => '🟢 Green — Swimming Allowed',
    'report_yellow' => '🟡 Yellow — Caution',
    'report_red' => '🔴 Red — Swimming Prohibited',
    'report_submit' => 'Submit Report',
    'report_thanks' => 'Thanks for your report! You earned :points points.',
    'report_rate_limit' => 'Too many attempts. Please wait a few seconds.',
    'report_error' => 'Error submitting report. Suspended account.',
    'report_confirm' => 'Are you sure? The flag at the beach is',
    'report_confirm_green' => '🟢 Green?',
    'report_confirm_yellow' => '🟡 Yellow?',
    'report_confirm_red' => '🔴 Red?',
    'report_success' => 'Flag updated! Your vote replaced the previous one.',
    'report_success_points' => 'Flag updated! You earned :points point(s).',
    'report_same_flag' => 'You already voted this color today. Pick a different color to change your vote.',

    // Dining
    'dining_title' => '🍴 Nearby Dining',
    'dining_booking' => 'Book a Table',
    'dining_view' => 'View Page',
    'dining_rating' => '★ :rating (:count reviews)',
    'dining_avg_price' => 'Average Price: :price €',
    'dining_distance' => 'Distance from beach: :distance km',
    'dining_no_results' => 'No restaurants found near this beach.',

    // Helper texts (prediction)
    'helper_stable' => 'Stable forecast with a clear trend.',
    'helper_mixed_green_yellow' => 'Mixed trend between Green and Yellow (transitional sea).',
    'helper_mixed_yellow_red' => 'Unstable trend between Yellow and Red (worsening sea).',
    'helper_volatile' => 'Volatile weather and sea conditions.',

    // Water quality
    'quality_excellent' => 'Excellent',
    'quality_good' => 'Good',
    'quality_sufficient' => 'Sufficient',
    'quality_poor' => 'Poor',
    'quality_unknown' => 'Unknown',
    'quality_unavailable' => 'No Data',
    'quality_analysis' => 'Analysis of :date',
    'quality_analysis_none' => 'No data',
    'quality_days' => ':days days',

    // UV Index
    'uv_very_high' => 'Very High ⚠️',
    'uv_high' => 'High',
    'uv_moderate' => 'Moderate',
    'uv_low' => 'Low',
    'uv_unavailable' => 'No Data',

    // Tide
    'tide_state' => 'Tide State',
    'tide_rising' => '▲ Rising',
    'tide_falling' => '▼ Falling',
    'tide_next' => 'Next: :tide at :time',
    'tide_high_name' => 'High Tide',
    'tide_low_name' => 'Low Tide',
    'tide_source' => 'OGC-IH Forecast',
    'tide_now' => 'Now',
    'tide_today_label' => 'Today',
    'tide_tomorrow_label' => 'Tomorrow',
    'tide_next_label' => 'Next',
    'tide_none' => 'No tide forecasts available.',

    // Moon
    'moon_current' => 'Current Moon Phase',
    'moon_illumination' => 'Illumination: :pct%',
    'moon_cycle' => 'Lunar Cycle (:currentd / :totald)',
    'moon_upcoming_phases' => 'Upcoming Moon Phases',
    'moon_in_days' => 'in :daysd',

    // Direction labels
    'dir_label' => 'Dir: :value',
    'min_label' => 'Min: :value',
    'precip_label' => 'Precip: :value',
    'no_rain' => 'No rain',
    'sst_avg' => 'SST Avg',

    // Misc
    'map_location' => 'Geographic Location',
    'gps_button' => 'GPS',
    'start_label' => 'Start: :date',
    'select_flag' => 'Select flag color',
];
