<?php

return [
    'page_title' => ':name - Bandera y Condiciones del Mar',
    'meta_description' => 'Bandera actual, temperatura del agua, olas, viento y pronóstico meteorológico para :name en :municipality.',
    'back_to_map' => '← Volver al Mapa',
    'region_badge' => ':region',

    // Flag section
    'flag_title' => 'Bandera Actual',
    'flag_official' => 'Recogida oficial de datos costeros.',
    'flag_community' => 'Con confirmación de la comunidad.',
    'flag_predicted' => 'Bandera pronosticada automáticamente.',
    'flag_off_season' => 'Playa fuera de temporada de baño.',
    'flag_verified_by' => 'Verificado por :count usuarios',
    'today_votes_title' => 'Confirmaciones de Hoy',
    'history_title' => 'Evolución Hoy',
    'flag_report' => 'Reportar Bandera',

    // Weather
    'weather_title' => 'Condiciones Actuales',
    'weather_air' => 'Temperatura del Aire',
    'weather_water' => 'Temperatura del Agua',
    'weather_waves' => 'Altura de las Olas',
    'weather_wind' => 'Velocidad y Dirección del Viento',
    'weather_humidity' => 'Humedad',
    'weather_uv' => 'Índice UV',
    'weather_wave_period' => 'Período',
    'weather_wave_direction' => 'Dirección',
    'weather_wind_speed' => ':speed km/h',
    'weather_wind_direction' => ':direction',

    // Tide
    'tide_title' => 'Marcas',
    'tide_high' => 'Marea Alta',
    'tide_low' => 'Marea Baja',
    'tide_next_high' => 'Próxima Marea Alta',
    'tide_next_low' => 'Próxima Marea Baja',
    'tide_today' => 'Marcas de Hoy',
    'tide_height' => ':height m',

    // Jellyfish
    'jellyfish_title' => 'Riesgo de Medusas',
    'jellyfish_low' => '🟢 Bajo',
    'jellyfish_moderate' => '🟡 Moderado',
    'jellyfish_high' => '🔴 Alto',
    'jellyfish_description' => 'Estimación basada en temperatura del agua, viento y estacionalidad.',

    // About section
    'about_title' => 'Sobre la Playa',
    'about_services' => 'Servicios Disponibles',

    // Moon
    'moon_title' => 'Calendario Lunar',
    'moon_phase' => 'Fase Lunar',
    'moon_spring_tide' => 'Marea Viva',
    'moon_neap_tide' => 'Marea Muerta',
    'moon_upcoming' => 'Próximas Fases',

    // Report
    'report_title' => 'Reportar Bandera',
    'report_description' => '¿Viste la bandera hoy? Ayuda a la comunidad.',
    'report_green' => '🟢 Verde — Baño Permitido',
    'report_yellow' => '🟡 Amarilla — Baño Vigilado',
    'report_red' => '🔴 Roja — Baño Prohibido',
    'report_submit' => 'Enviar Reporte',
    'report_thanks' => '¡Gracias por tu reporte! Ganaste :points puntos.',
    'report_rate_limit' => 'Demasiados intentos. Espera unos segundos.',
    'report_error' => 'Error al enviar el reporte. Cuenta suspendida.',
    'report_confirm' => '¿Estás seguro? La bandera en la playa es',
    'report_confirm_green' => '🟢 Verde?',
    'report_confirm_yellow' => '🟡 Amarilla?',
    'report_confirm_red' => '🔴 Roja?',
    'report_success' => '¡Bandera actualizada! Tu voto reemplazó al anterior.',
    'report_success_points' => '¡Bandera actualizada! Ganaste :points punto(s).',
    'report_same_flag' => 'Ya votaste este color hoy. Elige un color diferente para cambiar tu voto.',

    // Dining
    'dining_title' => '🍴 Dónde Comer Cerca',
    'dining_booking' => 'Reservar Mesa',
    'dining_view' => 'Ver Ficha',
    'dining_rating' => '★ :rating (:count reseñas)',
    'dining_avg_price' => 'Precio Medio: :price €',
    'dining_distance' => 'Distancia de la playa: :distance km',
    'dining_no_results' => 'No se encontraron restaurantes cerca de esta playa.',

    // Helper texts (prediction)
    'helper_stable' => 'Pronóstico estable con tendencia clara.',
    'helper_mixed_green_yellow' => 'Tendencia mixta entre Verde y Amarilla (mar de transición).',
    'helper_mixed_yellow_red' => 'Tendencia inestable entre Amarilla y Roja (mar empeorando).',
    'helper_volatile' => 'Condiciones meteorológicas y marítimas volátiles.',

    // Water quality
    'quality_excellent' => 'Excelente',
    'quality_good' => 'Buena',
    'quality_sufficient' => 'Suficiente',
    'quality_poor' => 'Inadecuada',
    'quality_unknown' => 'Desconocido',
    'quality_unavailable' => 'Sin Datos',
    'quality_analysis' => 'Análisis de :date',
    'quality_analysis_none' => 'Sin datos',
    'quality_days' => ':days días',

    // UV Index
    'uv_very_high' => 'Muy Alto ⚠️',
    'uv_high' => 'Alto',
    'uv_moderate' => 'Moderado',
    'uv_low' => 'Bajo',
    'uv_unavailable' => 'Sin Datos',

    // Tide
    'tide_state' => 'Estado de la Marea',
    'tide_rising' => '▲ Subiendo',
    'tide_falling' => '▼ Bajando',
    'tide_next' => 'Próxima: :tide a las :time',
    'tide_high_name' => 'Marea Alta',
    'tide_low_name' => 'Marea Baja',
    'tide_source' => 'Previsión OGC-IH',
    'tide_now' => 'Ahora',
    'tide_today_label' => 'Hoy',
    'tide_tomorrow_label' => 'Mañana',
    'tide_next_label' => 'Siguiente',
    'tide_none' => 'No hay previsiones de marea disponibles.',

    // Moon
    'moon_current' => 'Fase Lunar Actual',
    'moon_illumination' => 'Iluminación: :pct%',
    'moon_cycle' => 'Ciclo Lunar (:currentd / :totald)',
    'moon_upcoming_phases' => 'Próximas Fases Lunares',
    'moon_in_days' => 'en :daysd',

    // Direction labels
    'dir_label' => 'Dir: :value',
    'min_label' => 'Mín: :value',
    'precip_label' => 'Precip: :value',
    'no_rain' => 'Sin lluvia',
    'sst_avg' => 'SST Media',

    // Misc
    'map_location' => 'Ubicación Geográfica',
    'gps_button' => 'GPS',
    'start_label' => 'Inicio: :date',
    'select_flag' => 'Seleccionar color de bandera',
];
