<?php

return [
    'page_title' => ':name - Bandeira e Condições do Mar',
    'meta_description' => 'Bandeira atual, temperatura da água, ondas, vento e previsão meteorológica para :name em :municipality.',
    'back_to_map' => '← Voltar ao Mapa',
    'region_badge' => ':region',

    // Flag section
    'flag_title' => 'Bandeira Actual',
    'flag_official' => 'Recolha oficial de dados costeiros.',
    'flag_community' => 'Com confirmação da comunidade.',
    'flag_user_vote' => 'Voto de Utilizador',
    'flag_predicted' => 'Bandeira prevista automaticamente.',
    'flag_off_season' => 'Praia fora da época balnear.',
    'flag_verified_by' => 'Verificado por :count utilizadores',
    'today_votes_title' => 'Confirmações de Hoje',
    'history_title' => 'Evolução Hoje',
    'history_entries' => 'registos',
    'history_show_more' => 'Ver mais :count registos',
    'history_show_less' => 'Mostrar menos',
    'flag_report' => 'Reportar Bandeira',

    // Weather
    'weather_title' => 'Condições Actuais',
    'weather_air' => 'Temperatura do Ar',
    'weather_water' => 'Temperatura da Água',
    'weather_waves' => 'Altura das Ondas',
    'weather_wind' => 'Velocidade e Direção do Vento',
    'weather_humidity' => 'Humidade',
    'weather_uv' => 'Índice UV',
    'weather_wave_period' => 'Período',
    'weather_wave_direction' => 'Direção',
    'weather_wind_speed' => ':speed km/h',
    'weather_wind_direction' => ':direction',

    // Tide
    'tide_title' => 'Marés',
    'tide_high' => 'Maré Alta',
    'tide_low' => 'Maré Baixa',
    'tide_next_high' => 'Próxima Maré Alta',
    'tide_next_low' => 'Próxima Maré Baixa',
    'tide_today' => 'Marés de Hoje',
    'tide_height' => ':height m',

    // Jellyfish
    'jellyfish_title' => 'Risco de Alforrecas',
    'jellyfish_low' => '🟢 Baixo',
    'jellyfish_moderate' => '🟡 Moderado',
    'jellyfish_high' => '🔴 Alto',
    'jellyfish_description' => 'Estimativa baseada na temperatura da água, vento e sazonalidade.',

    // About section (redundant keys kept for namespace consistency)
    'about_title' => 'Sobre a Praia',
    'about_services' => 'Serviços Disponíveis',

    // Moon
    'moon_title' => 'Calendário Lunar',
    'moon_phase' => 'Fase da Lua',
    'moon_spring_tide' => 'Maré Viva',
    'moon_neap_tide' => 'Maré Morta',
    'moon_upcoming' => 'Próximas Fases',

    // Report
    'report_title' => 'Reportar Bandeira',
    'report_description' => 'Viste a bandeira hoje? Ajuda a comunidade.',
    'report_green' => '🟢 Verde — Banho Permitido',
    'report_yellow' => '🟡 Amarela — Banho Vigilado',
    'report_red' => '🔴 Vermelha — Banho Proibido',
    'report_submit' => 'Submeter Reporte',
    'report_thanks' => 'Obrigado pelo teu reporte! Ganhaste :points pontos.',
    'report_rate_limit' => 'Muitas tentativas. Aguarda alguns segundos.',
    'report_error' => 'Erro ao submeter reporte. Conta suspensa.',
    'report_confirm' => 'Tens a certeza? A bandeira na praia é',
    'report_confirm_green' => '🟢 Verde?',
    'report_confirm_yellow' => '🟡 Amarela?',
    'report_confirm_red' => '🔴 Vermelha?',
    'report_success' => 'Bandeira atualizada! O teu voto substituiu o anterior.',
    'report_success_points' => 'Bandeira atualizada! Ganhaste :points ponto(s).',
    'report_same_flag' => 'Já votaste nesta cor hoje. Escolhe uma cor diferente para alterar o teu voto.',
    'no_lifeguards_warning' => 'Fora do horário de vigilância (:start às :end). Não existem nadadores-salvadores de serviço.',
    'report_outside_lifeguard_hours' => 'A votação só está disponível durante o horário dos nadadores-salvadores (:start às :end).',
    'report_too_far' => 'Estás a :distance km da praia. A votação só é permitida até :max km de distância.',
    'admin_override_active' => 'Modo admin ativo — podes votar a qualquer hora e sem limite de distância.',

    // Dining
    'dining_title' => '🍴 Onde Comer por Perto',
    'dining_booking' => 'Reservar Mesa',
    'dining_view' => 'Ver Ficha',
    'dining_rating' => '★ :rating (:count avaliações)',
    'dining_avg_price' => 'Preço Médio: :price €',
    'dining_distance' => 'Distância da praia: :distance km',
    'dining_no_results' => 'Nenhum restaurante encontrado perto desta praia.',

    // Helper texts (prediction)
    'helper_stable' => 'Previsão estável com tendência clara.',
    'helper_mixed_green_yellow' => 'Tendência mista entre Verde e Amarela (mar de transição).',
    'helper_mixed_yellow_red' => 'Tendência instável entre Amarela e Vermelha (mar a piorar).',
    'helper_volatile' => 'Condições meteorológicas e marítimas voláteis.',

    // Water quality
    'quality_excellent' => 'Excelente',
    'quality_good' => 'Boa',
    'quality_sufficient' => 'Suficiente',
    'quality_poor' => 'Imprópria',
    'quality_unknown' => 'Desconhecido',
    'quality_unavailable' => 'Sem Dados',
    'quality_analysis' => 'Análise de :date',
    'quality_analysis_none' => 'Sem dados',
    'quality_days' => ':days dias',

    // UV Index
    'uv_very_high' => 'Muito Alto ⚠️',
    'uv_high' => 'Alto',
    'uv_moderate' => 'Moderado',
    'uv_low' => 'Baixo',
    'uv_unavailable' => 'Sem Dados',

    // Tide
    'tide_state' => 'Estado da Maré',
    'tide_rising' => '▲ A encher',
    'tide_falling' => '▼ A vazar',
    'tide_next' => 'Próxima: :tide às :time',
    'tide_high_name' => 'Preia-mar',
    'tide_low_name' => 'Baixa-mar',
    'tide_source' => 'Previsão OGC-IH',
    'tide_now' => 'Agora',
    'tide_today_label' => 'Hoje',
    'tide_tomorrow_label' => 'Amanhã',
    'tide_next_label' => 'A seguir',
    'tide_none' => 'Sem previsões de marés disponíveis.',

    // Moon
    'moon_current' => 'Fase Lunar Atual',
    'moon_illumination' => 'Iluminação: :pct%',
    'moon_cycle' => 'Ciclo Lunar (:currentd / :totald)',
    'moon_upcoming_phases' => 'Próximas Fases Lunares',
    'moon_in_days' => 'em :daysd',

    // Direction labels
    'dir_label' => 'Dir: :value',
    'min_label' => 'Mín: :value',
    'precip_label' => 'Precip: :value',
    'no_rain' => 'Sem chuva',
    'sst_avg' => 'SST Média',

    // Misc
    'map_location' => 'Localização Geográfica',
    'gps_button' => 'GPS',
    'start_label' => 'Início: :date',
    'select_flag' => 'Selecionar cor da bandeira',

    // Weather forecast
    'weather_forecast_title' => 'Previsão Meteorológica',
];
