<?php

return [
    'page_title' => ':name - Drapeau et Conditions Maritimes',
    'meta_description' => 'Drapeau actuel, température de l\'eau, vagues, vent et prévisions météorologiques pour :name à :municipality.',
    'back_to_map' => '← Retour à la Carte',
    'region_badge' => ':region',

    // Flag section
    'flag_title' => 'Drapeau Actuel',
    'flag_official' => 'Collecte officielle de données côtières.',
    'flag_community' => 'Avec confirmation de la communauté.',
    'flag_user_vote' => "Vote de l'Utilisateur",
    'flag_predicted' => 'Drapeau prévu automatiquement.',
    'flag_off_season' => 'Plage hors saison de baignade.',
    'flag_verified_by' => 'Vérifié par :count utilisateurs',
    'today_votes_title' => 'Confirmations d\'Aujourd\'hui',
    'history_title' => 'Évolution Aujourd\'hui',
    'flag_report' => 'Signaler le Drapeau',

    // Weather
    'weather_title' => 'Conditions Actuelles',
    'weather_air' => 'Température de l\'Air',
    'weather_water' => 'Température de l\'Eau',
    'weather_waves' => 'Hauteur des Vagues',
    'weather_wind' => 'Vitesse et Direction du Vent',
    'weather_humidity' => 'Humidité',
    'weather_uv' => 'Indice UV',
    'weather_wave_period' => 'Période',
    'weather_wave_direction' => 'Direction',
    'weather_wind_speed' => ':speed km/h',
    'weather_wind_direction' => ':direction',

    // Tide
    'tide_title' => 'Marées',
    'tide_high' => 'Marée Haute',
    'tide_low' => 'Marée Basse',
    'tide_next_high' => 'Prochaine Marée Haute',
    'tide_next_low' => 'Prochaine Marée Basse',
    'tide_today' => 'Marées du Jour',
    'tide_height' => ':height m',

    // Jellyfish
    'jellyfish_title' => 'Risque de Méduses',
    'jellyfish_low' => '🟢 Faible',
    'jellyfish_moderate' => '🟡 Modéré',
    'jellyfish_high' => '🔴 Élevé',
    'jellyfish_description' => 'Estimation basée sur la température de l\'eau, le vent et la saisonnalité.',

    // About section
    'about_title' => 'À Propos de la Plage',
    'about_services' => 'Services Disponibles',

    // Moon
    'moon_title' => 'Calendrier Lunaire',
    'moon_phase' => 'Phase Lunaire',
    'moon_spring_tide' => 'Marée Vive',
    'moon_neap_tide' => 'Marée Morte',
    'moon_upcoming' => 'Prochaines Phases',

    // Report
    'report_title' => 'Signaler le Drapeau',
    'report_description' => 'As-tu vu le drapeau aujourd\'hui ? Aide la communauté.',
    'report_green' => '🟢 Vert — Baignade Autorisée',
    'report_yellow' => '🟡 Jaune — Baignade Surveillée',
    'report_red' => '🔴 Rouge — Baignade Interdite',
    'report_submit' => 'Soumettre le Signalement',
    'report_thanks' => 'Merci pour ton signalement ! Tu as gagné :points points.',
    'report_rate_limit' => 'Trop de tentatives. Attends quelques secondes.',
    'report_error' => 'Erreur lors de l\'envoi du signalement. Compte suspendu.',
    'report_confirm' => 'Es-tu sûr ? Le drapeau sur la plage est',
    'report_confirm_green' => '🟢 Vert ?',
    'report_confirm_yellow' => '🟡 Jaune ?',
    'report_confirm_red' => '🔴 Rouge ?',
    'report_success' => 'Drapeau mis à jour ! Ton vote a remplacé le précédent.',
    'report_success_points' => 'Drapeau mis à jour ! Tu as gagné :points point(s).',
    'report_same_flag' => 'Tu as déjà voté cette couleur aujourd\'hui. Choisis une couleur différente pour modifier ton vote.',
    'no_lifeguards_warning' => 'Hors des heures de surveillance (:start à :end). Aucun sauveteur en service.',
    'report_outside_lifeguard_hours' => 'Le vote est uniquement disponible pendant les heures de surveillance (:start à :end).',

    // Dining
    'dining_title' => '🍴 Où Manger à Proximité',
    'dining_booking' => 'Réserver une Table',
    'dining_view' => 'Voir la Fiche',
    'dining_rating' => '★ :rating (:count avis)',
    'dining_avg_price' => 'Prix Moyen : :price €',
    'dining_distance' => 'Distance de la plage : :distance km',
    'dining_no_results' => 'Aucun restaurant trouvé près de cette plage.',

    // Helper texts (prediction)
    'helper_stable' => 'Prévision stable avec une tendance claire.',
    'helper_mixed_green_yellow' => 'Tendance mixte entre Vert et Jaune (mer de transition).',
    'helper_mixed_yellow_red' => 'Tendance instable entre Jaune et Rouge (mer qui se dégrade).',
    'helper_volatile' => 'Conditions météorologiques et maritimes volatiles.',

    // Water quality
    'quality_excellent' => 'Excellent',
    'quality_good' => 'Bonne',
    'quality_sufficient' => 'Suffisante',
    'quality_poor' => 'Mauvaise',
    'quality_unknown' => 'Inconnu',
    'quality_unavailable' => 'Aucune Donnée',
    'quality_analysis' => 'Analyse du :date',
    'quality_analysis_none' => 'Aucune donnée',
    'quality_days' => ':days jours',

    // UV Index
    'uv_very_high' => 'Très Élevé ⚠️',
    'uv_high' => 'Élevé',
    'uv_moderate' => 'Modéré',
    'uv_low' => 'Faible',
    'uv_unavailable' => 'Aucune Donnée',

    // Tide
    'tide_state' => 'État de la Marée',
    'tide_rising' => '▲ Montante',
    'tide_falling' => '▼ Descendante',
    'tide_next' => 'Prochaine : :tide à :time',
    'tide_high_name' => 'Marée Haute',
    'tide_low_name' => 'Marée Basse',
    'tide_source' => 'Prévision OGC-IH',
    'tide_now' => 'Maintenant',
    'tide_today_label' => 'Aujourd\'hui',
    'tide_tomorrow_label' => 'Demain',
    'tide_next_label' => 'Suivant',
    'tide_none' => 'Aucune prévision de marée disponible.',

    // Moon
    'moon_current' => 'Phase Lunaire Actuelle',
    'moon_illumination' => 'Illumination : :pct%',
    'moon_cycle' => 'Cycle Lunaire (:currentd / :totald)',
    'moon_upcoming_phases' => 'Prochaines Phases Lunaires',
    'moon_in_days' => 'dans :daysd',

    // Direction labels
    'dir_label' => 'Dir : :value',
    'min_label' => 'Min : :value',
    'precip_label' => 'Précip : :value',
    'no_rain' => 'Pas de pluie',
    'sst_avg' => 'SST Moy',

    // Misc
    'map_location' => 'Localisation Géographique',
    'gps_button' => 'GPS',
    'start_label' => 'Début : :date',
    'select_flag' => 'Sélectionner la couleur du drapeau',
];
