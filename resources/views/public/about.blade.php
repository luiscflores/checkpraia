@props(['title' => __('common.about_page_title')])

<x-layouts.app>
    <x-slot:title>{{ $title }}</x-slot:title>

    @section('meta_description', __('common.about_page_subtitle'))
    @section('og_title', $title)
    @section('og_description', __('common.about_page_subtitle'))
    @section('og_type', 'website')

    @section('ld_json')
    @parent
    <script type="application/ld+json">
    @php echo json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        '@id' => url('/sobre') . '#webpage',
        'name' => $title,
        'description' => __('common.about_page_subtitle'),
        'url' => url('/sobre'),
        'isPartOf' => ['@id' => url('/') . '#website'],
        'about' => [
            '@type' => 'Thing',
            'name' => 'CheckPraia - Informação de Praias Portuguesas',
            'description' => __('common.about_page_mission'),
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); @endphp
    </script>
    @endsection

    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 space-y-10">
        {{-- Hero --}}
        <div class="text-center space-y-3">
            <div class="text-5xl mb-2">🏖️</div>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">{{ $title }}</h1>
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

        {{-- Install PWA --}}
        <div x-data="pwaInstallHandler()" class="glass-card p-6 sm:p-8 rounded-2xl space-y-4 text-center" x-cloak>
            <div class="flex justify-center">
                <div class="p-1 rounded-2xl bg-gradient-to-br from-blue-600/20 to-slate-800/10 shadow-lg shadow-blue-600/20 ring-1 ring-blue-400/10">
                    <img src="/icon-192.svg" alt="CheckPraia" class="w-16 h-16">
                </div>
            </div>
            <div class="space-y-2">
                <h2 class="text-lg font-bold">{{ __('common.about_page_install_title') }}</h2>
                <p class="text-sm text-theme-secondary leading-relaxed">{{ __('common.about_page_install_description') }}</p>
            </div>
            <button
                @click="install()"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm px-6 py-3 rounded-xl shadow-lg shadow-blue-600/20 transition-all active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                {{ __('common.about_page_install_button') }}
            </button>
        </div>

        <x-ads.slot slot="about_bottom" />
    </div>
</x-layouts.app>
