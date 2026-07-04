@props(['title' => __('common.about_page_title')])

<x-layouts.app>
    <x-slot:title>{{ $title }}</x-slot:title>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 space-y-10">
        {{-- Hero --}}
        <div class="text-center space-y-3">
            <div class="text-5xl mb-2">🏖️</div>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">{{ __('common.about_page_title') }}</h1>
            <p class="text-theme-secondary max-w-xl mx-auto leading-relaxed">{{ __('common.about_page_subtitle') }}</p>
        </div>

        {{-- Mission --}}
        <div class="glass-card p-6 sm:p-8 rounded-2xl space-y-3">
            <h2 class="text-lg font-bold flex items-center gap-2"><span>🎯</span> {{ __('common.about_page_mission_title') }}</h2>
            <p class="text-theme-secondary leading-relaxed">{{ __('common.about_page_mission') }}</p>
        </div>

        {{-- How It Works --}}
        <div class="space-y-4">
            <h2 class="text-xl font-bold text-center">{{ __('common.about_page_how_title') }}</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="glass-card p-5 rounded-2xl space-y-2 text-center">
                    <span class="text-3xl block">🚩</span>
                    <h3 class="font-bold text-sm">{{ __('common.about_page_how_step1_title') }}</h3>
                    <p class="text-xs text-theme-secondary leading-relaxed">{{ __('common.about_page_how_step1') }}</p>
                </div>
                <div class="glass-card p-5 rounded-2xl space-y-2 text-center">
                    <span class="text-3xl block">🌤️</span>
                    <h3 class="font-bold text-sm">{{ __('common.about_page_how_step2_title') }}</h3>
                    <p class="text-xs text-theme-secondary leading-relaxed">{{ __('common.about_page_how_step2') }}</p>
                </div>
                <div class="glass-card p-5 rounded-2xl space-y-2 text-center">
                    <span class="text-3xl block">🤝</span>
                    <h3 class="font-bold text-sm">{{ __('common.about_page_how_step3_title') }}</h3>
                    <p class="text-xs text-theme-secondary leading-relaxed">{{ __('common.about_page_how_step3') }}</p>
                </div>
            </div>
        </div>

        {{-- Features --}}
        <div class="space-y-4">
            <h2 class="text-xl font-bold text-center">{{ __('common.about_page_features_title') }}</h2>
            <div class="grid gap-3 sm:grid-cols-2">
                @php
                    $features = [
                        ['🚩', 'about_page_feature_live'],
                        ['🌤️', 'about_page_feature_weather'],
                        ['🗳️', 'about_page_feature_community'],
                        ['⭐', 'about_page_feature_favorites'],
                        ['🔔', 'about_page_feature_alerts'],
                        ['🗺️', 'about_page_feature_maps'],
                        ['📡', 'about_page_feature_offline'],
                        ['🌐', 'about_page_feature_multilang'],
                    ];
                @endphp
                @foreach($features as [$icon, $key])
                    <div class="flex items-center gap-3 glass-card px-4 py-3 rounded-xl">
                        <span class="text-xl">{{ $icon }}</span>
                        <span class="text-sm font-semibold">{{ __("common.{$key}") }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <x-ads.slot slot="about_bottom" />
    </div>
</x-layouts.app>
