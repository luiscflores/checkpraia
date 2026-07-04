<?php

return [
    'labels' => [
        'green' => 'Verde',
        'yellow' => 'Amarela',
        'red' => 'Vermelha',
        'blue_or_neutral' => 'Fora de Época',
        'gray' => 'Sem Info',
    ],

    'icons' => [
        'green' => '🟢',
        'yellow' => '🟡',
        'red' => '🔴',
        'blue_or_neutral' => '🔵',
        'gray' => '⚪',
    ],

    'colors' => [
        'green' => ['hex' => '#10b981', 'tailwind_bg' => 'bg-emerald-500', 'tailwind_text' => 'text-emerald-400', 'rgba_glow' => 'rgba(16,185,129,0.4)'],
        'yellow' => ['hex' => '#f59e0b', 'tailwind_bg' => 'bg-amber-500', 'tailwind_text' => 'text-amber-400', 'rgba_glow' => 'rgba(245,158,11,0.4)'],
        'red' => ['hex' => '#ef4444', 'tailwind_bg' => 'bg-rose-500', 'tailwind_text' => 'text-rose-400', 'rgba_glow' => 'rgba(239,68,68,0.4)'],
        'blue_or_neutral' => ['hex' => '#3b82f6', 'tailwind_bg' => 'bg-blue-500', 'tailwind_text' => 'text-blue-400', 'rgba_glow' => 'rgba(59,130,246,0.3)'],
        'gray' => ['hex' => '#6b7280', 'tailwind_bg' => 'bg-slate-500', 'tailwind_text' => 'text-slate-400', 'rgba_glow' => 'rgba(107,114,128,0.2)'],
    ],

    'marker_colors' => [
        'green' => '#10b981',
        'yellow' => '#f59e0b',
        'red' => '#ef4444',
        'blue_or_neutral' => '#3b82f6',
        'gray' => '#6b7280',
    ],

    'default' => 'gray',
];
