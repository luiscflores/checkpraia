<div class="space-y-3 sm:space-y-6" x-data="beachMapHandler(@js($mapBeaches), @js($defaultRegion ?? null))">

@pushOnce('leaflet-css')
<link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.min.css') }}" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.min.css') }}"></noscript>
@endPushOnce

@pushOnce('leaflet-js')
<script src="{{ asset('vendor/leaflet/leaflet.min.js') }}" defer></script>
@endPushOnce

    @section('title', __('home.title'))
    @section('og_title', __('home.og_title'))
    @section('og_description', __('home.og_description'))
    @section('hreflang')
        @foreach(config('locales.supported', ['pt', 'en', 'es', 'fr']) as $locale)
            <link rel="alternate" hreflang="{{ $locale }}" href="{{ url($locale === 'pt' ? '/' : "/{$locale}") }}">
        @endforeach
    @endsection

    <h1 class="sr-only">{{ __('home.page_title') }}</h1>

    <div class="sr-only" aria-hidden="true">
        <p>{{ __('home.og_description') }}</p>
    </div>
    @section('ld_json')
    @parent
    <script type="application/ld+json">
    @php echo json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        '@id' => url('/') . '#faq',
        'mainEntity' => [
            [
                '@type' => 'Question',
                'name' => 'Qual é a bandeira atual das praias de Portugal?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Consulta o mapa do CheckPraia para ver a bandeira mais provável de cada praia marítima vigiada de Portugal, incluindo Açores e Madeira.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'Como saber a temperatura da água do mar nas praias portuguesas?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'O CheckPraia mostra a temperatura da água (SST), altura das ondas, direção do vento e previsão meteorológica para cada praia.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'A bandeira do CheckPraia é oficial?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'A bandeira apresentada pelo CheckPraia resulta de previsões automáticas e confirmações da comunidade. Não substitui a informação oficial dos nadadores-salvadores na praia.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'Como posso reportar a bandeira que vejo na praia?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Ao visitares a página de detalhe de uma praia no CheckPraia, podes votar na bandeira que estás a ver, contribuindo para a comunidade.',
                ],
            ],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); @endphp
    </script>
    @endsection

    <div x-data="{ toastMessage: null, toastVisible: false }"
         x-cloak
         x-on:favorite-error.window="toastMessage = $event.detail.message; toastVisible = true; setTimeout(() => toastVisible = false, 3000)"
         x-show="toastVisible"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed top-4 left-1/2 -translate-x-1/2 z-[60] max-w-sm w-full mx-auto px-4 pointer-events-none">
        <div class="bg-rose-600/90 backdrop-blur-md text-white text-sm font-medium px-4 py-3 rounded-xl shadow-2xl border border-rose-400/30 flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            <span x-text="toastMessage"></span>
        </div>
    </div>

    <!-- Search and Filters Panel -->
    <div class="glass-card p-4 rounded-3xl border border-theme-subtle/50 space-y-4 shadow-lg shadow-black/[0.02] animate-fade-in-up" x-data="{ searchFocused: false, flagOpen: false }">
        <div class="flex items-stretch gap-2.5">
            <div class="w-full relative flex-1">
                <label for="beach-search" class="sr-only">{{ __('common.search_placeholder') }}</label>
                <input 
                    id="beach-search"
                    type="text" 
                    wire:model.live.debounce.400ms="search" 
                    placeholder="{{ __('common.search_placeholder') }}" 
                    @focus="searchFocused = true" @blur="searchFocused = false"
                    aria-label="{{ __('common.search_placeholder') }}"
                    class="w-full bg-theme-input border border-theme-subtle/60 px-4 py-3.5 pl-11 pr-10 rounded-2xl text-base sm:text-sm text-theme placeholder:text-theme-muted focus:outline-none focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/10 transition-all shadow-inner"
                />
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4.5 h-4.5 text-theme-muted transition-colors" :class="searchFocused && 'text-blue-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                @if(strlen($search) > 0)
                    <button wire:click="$set('search', '')" class="absolute right-3 top-1/2 -translate-y-1/2 text-theme-muted hover:text-theme transition-colors p-2 touch-target" aria-label="{{ __('common.search_clear') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                @endif
            </div>

            <button @click="locateUser()" class="shrink-0 bg-theme-card active:scale-90 text-theme text-sm font-semibold px-4 sm:px-5 py-3.5 rounded-2xl border border-theme-subtle/60 hover:border-blue-500/30 transition-all flex items-center justify-center gap-2 shadow-sm touch-target group" title="{{ __('common.search_nearby') }}" aria-label="{{ __('common.search_nearby') }}">
                <svg class="w-5 h-5 text-blue-400 group-hover:text-blue-300 group-hover:animate-pulse transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span class="hidden sm:inline">{{ __('common.search_nearby_short') }}</span>
            </button>

            <template x-teleport="body">
                <div x-show="locationError" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2" class="fixed bottom-28 md:bottom-24 left-1/2 -translate-x-1/2 z-[60] max-w-sm w-full mx-auto px-4" role="alert">
                    <div class="bg-red-600/90 backdrop-blur-md text-white text-sm font-medium px-4 py-3 rounded-xl shadow-2xl border border-red-400/30 flex items-center gap-2">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        <span x-text="locationError" class="flex-1"></span>
                        <button @click="locationError = null" class="shrink-0 p-2 hover:bg-white/10 rounded-lg transition-colors touch-target" aria-label="Dismiss">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Flag Filter Dropdown -->
        <div class="relative" x-data="{ dropdownPos: { top: 0, left: 0, width: 0 } }">
            <button x-ref="flagTrigger" @click="flagOpen = !flagOpen; if(flagOpen) { const r = $refs.flagTrigger.getBoundingClientRect(); dropdownPos = { top: r.bottom + 8, left: r.left, width: r.width } }"
                    class="relative z-[10000] flex items-center gap-2.5 px-4 py-2.5 rounded-2xl text-xs font-bold transition-all border min-h-[42px] w-full sm:w-auto sm:self-start
                    @if($selectedFlag === 'green') bg-emerald-600/10 border-emerald-500/30 text-emerald-400
                    @elseif($selectedFlag === 'yellow') bg-amber-600/10 border-amber-500/30 text-amber-400
                    @elseif($selectedFlag === 'red') bg-rose-600/10 border-rose-500/30 text-rose-400
                    @elseif($selectedFlag === 'blue_or_neutral') bg-blue-600/10 border-blue-500/30 text-blue-400
                    @elseif($selectedFlag === 'gray') bg-slate-600/10 border-slate-500/30 text-slate-400
                    @else bg-blue-600/10 border-blue-500/30 text-blue-400
                    @endif shadow-sm"
                    aria-haspopup="listbox" :aria-expanded="flagOpen">
                @if($selectedFlag === 'green')
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
                    <span>{{ __('common.flag_green') }}</span>
                @elseif($selectedFlag === 'yellow')
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shrink-0"></span>
                    <span>{{ __('common.flag_yellow') }}</span>
                @elseif($selectedFlag === 'red')
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shrink-0"></span>
                    <span>{{ __('common.flag_red') }}</span>
                @elseif($selectedFlag === 'blue_or_neutral')
                    <span>❄️</span>
                    <span>{{ __('common.flag_blue_or_neutral') }}</span>
                @elseif($selectedFlag === 'gray')
                    <span class="w-2.5 h-2.5 rounded-full bg-slate-500 shrink-0"></span>
                    <span>{{ __('common.flag_none') }}</span>
                @else
                    <span>🏁</span>
                    <span>{{ __('common.flag_all') }}</span>
                @endif
                <svg class="w-3.5 h-3.5 shrink-0 transition-transform duration-200" :class="flagOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <template x-teleport="body">
                <div x-show="flagOpen" x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                     :style="'position:fixed; top:' + dropdownPos.top + 'px; left:' + dropdownPos.left + 'px; min-width:' + dropdownPos.width + 'px; z-index:9999'"
                     class="bg-theme-card/95 backdrop-blur-xl border border-theme-subtle/60 rounded-2xl shadow-xl shadow-black/10 py-1.5 overflow-hidden"
                     @click.stop
                     role="listbox" aria-label="{{ __('common.flag_all') }}">

                    <button @click="$wire.set('selectedFlag', ''); flagOpen = false"
                            class="w-full flex items-center gap-3 px-4 py-2.5 text-xs font-bold transition-colors min-h-[42px]
                            @if($selectedFlag === '') bg-blue-600/10 text-blue-400 @else text-theme-secondary hover:bg-theme-subtle/50 hover:text-theme @endif"
                            role="option" @if($selectedFlag === '') aria-selected="true" @endif>
                        <span>🏁</span>
                        <span>{{ __('common.flag_all') }}</span>
                        @if($selectedFlag === '')<span class="ml-auto text-blue-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></span>@endif
                    </button>

                    <button @click="$wire.set('selectedFlag', 'green'); flagOpen = false"
                            class="w-full flex items-center gap-3 px-4 py-2.5 text-xs font-bold transition-colors min-h-[42px]
                            @if($selectedFlag === 'green') bg-emerald-600/10 text-emerald-400 @else text-theme-secondary hover:bg-theme-subtle/50 hover:text-theme @endif"
                            role="option" @if($selectedFlag === 'green') aria-selected="true" @endif>
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
                        <span>{{ __('common.flag_green') }}</span>
                        @if($selectedFlag === 'green')<span class="ml-auto text-emerald-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></span>@endif
                    </button>

                    <button @click="$wire.set('selectedFlag', 'yellow'); flagOpen = false"
                            class="w-full flex items-center gap-3 px-4 py-2.5 text-xs font-bold transition-colors min-h-[42px]
                            @if($selectedFlag === 'yellow') bg-amber-600/10 text-amber-400 @else text-theme-secondary hover:bg-theme-subtle/50 hover:text-theme @endif"
                            role="option" @if($selectedFlag === 'yellow') aria-selected="true" @endif>
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shrink-0"></span>
                        <span>{{ __('common.flag_yellow') }}</span>
                        @if($selectedFlag === 'yellow')<span class="ml-auto text-amber-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></span>@endif
                    </button>

                    <button @click="$wire.set('selectedFlag', 'red'); flagOpen = false"
                            class="w-full flex items-center gap-3 px-4 py-2.5 text-xs font-bold transition-colors min-h-[42px]
                            @if($selectedFlag === 'red') bg-rose-600/10 text-rose-400 @else text-theme-secondary hover:bg-theme-subtle/50 hover:text-theme @endif"
                            role="option" @if($selectedFlag === 'red') aria-selected="true" @endif>
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shrink-0"></span>
                        <span>{{ __('common.flag_red') }}</span>
                        @if($selectedFlag === 'red')<span class="ml-auto text-rose-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></span>@endif
                    </button>

                    <button @click="$wire.set('selectedFlag', 'blue_or_neutral'); flagOpen = false"
                            class="w-full flex items-center gap-3 px-4 py-2.5 text-xs font-bold transition-colors min-h-[42px]
                            @if($selectedFlag === 'blue_or_neutral') bg-blue-600/10 text-blue-400 @else text-theme-secondary hover:bg-theme-subtle/50 hover:text-theme @endif"
                            role="option" @if($selectedFlag === 'blue_or_neutral') aria-selected="true" @endif>
                        <span>❄️</span>
                        <span>{{ __('common.flag_blue_or_neutral') }}</span>
                        @if($selectedFlag === 'blue_or_neutral')<span class="ml-auto text-blue-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></span>@endif
                    </button>

                    <button @click="$wire.set('selectedFlag', 'gray'); flagOpen = false"
                            class="w-full flex items-center gap-3 px-4 py-2.5 text-xs font-bold transition-colors min-h-[42px]
                            @if($selectedFlag === 'gray') bg-slate-600/10 text-slate-400 @else text-theme-secondary hover:bg-theme-subtle/50 hover:text-theme @endif"
                            role="option" @if($selectedFlag === 'gray') aria-selected="true" @endif>
                        <span class="w-2.5 h-2.5 rounded-full bg-slate-500 shrink-0"></span>
                        <span>{{ __('common.flag_none') }}</span>
                        @if($selectedFlag === 'gray')<span class="ml-auto text-slate-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></span>@endif
                    </button>
                </div>
            </template>
        </div>
        <!-- Backdrop to close dropdown on outside click -->
        <div x-show="flagOpen" @click="flagOpen = false" x-cloak x-init="$watch('flagOpen', v => { if(v) { const close = () => { flagOpen = false; window.removeEventListener('scroll', close); }; window.addEventListener('scroll', close, {once:true, passive:true}); } })" class="fixed inset-0 z-[9998]" style="display:none;"></div>
    </div>

    <!-- Nearby Beaches (closest 5) -->
    <div x-show="nearby !== null && nearby.length > 0"
         x-cloak
         class="animate-fade-in-up"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-extrabold text-theme flex items-center gap-2">
                <span aria-hidden="true">📍</span>
                <span>{{ __('home.nearby_title') }}</span>
            </h2>
            <span x-show="nearby.length > 0"
                  class="text-[11px] text-blue-400 font-bold bg-blue-500/10 border border-blue-500/20 px-2 py-0.5 rounded-full">
                <span x-text="nearby.length"></span> {{ __('home.nearby_count') }}
            </span>
        </div>

        <!-- Results grid -->
        <div x-show="nearby.length > 0"
             class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-2.5">
            <template x-for="(beach, i) in nearby" :key="beach.id">
                <a :href="beach.url"
                   class="glass-card card-lift p-3 rounded-2xl border border-theme-medium hover:border-blue-500/40 transition-all duration-300 group flex items-start gap-3">
                    <span class="shrink-0 w-8 h-8 rounded-full bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-sm font-extrabold text-blue-400"
                          x-text="i + 1"></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-theme group-hover:text-blue-400 transition-colors truncate">
                            <span x-show="beach.beachcam_slug" class="inline-block mr-1 shrink-0 bg-amber-600/20 border border-amber-500/25 text-amber-400 text-[10px] px-1.5 py-0.5 rounded-md font-bold leading-none">📹</span>
                            <span x-text="beach.name"></span>
                        </p>
                        <p class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1.5">
                            <span x-text="beach.municipality"></span>
                            <span class="text-blue-400 font-bold">·</span>
                            <span class="text-blue-400 font-bold" x-text="beach.distance_km + ' km'"></span>
                        </p>
                        <div class="flex items-center gap-2 mt-1">
                            <span x-show="beach.air_temp !== null" class="text-[11px] text-slate-400" x-text="'🌡️ ' + beach.air_temp + '°'"></span>
                            <span x-show="beach.water_temp !== null" class="text-[11px] text-slate-400" x-text="'💧 ' + beach.water_temp + '°'"></span>
                            <span x-show="beach.wave_height_max !== null" class="text-[11px] text-slate-400" x-text="'🌊 ' + beach.wave_height_max + 'm'"></span>
                        </div>
                    </div>
                </a>
            </template>
        </div>


    </div>

    <!-- Split Content View -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 sm:gap-6 min-h-[450px] sm:min-h-[550px]">
        
        <!-- Left Side: Beach Cards List -->
        <div id="beach-scroll-container" class="lg:col-span-5 flex flex-col gap-4 lg:max-h-[720px] lg:overflow-y-auto pr-0.5 sm:pr-2 lg:pb-6 scrollbar-thin" :class="viewState === 'list' ? 'flex' : 'hidden lg:flex'">
            <x-ads.slot slot="home_sidebar_top" className="col-span-full mx-1" />

            <div class="flex flex-col gap-4" x-data="cardFavorites(@js($beachesList))" x-ref="beachList">

            @forelse($beachesList as $i => $beach)
                @if($i > 0 && $i % 3 === 0)
                    <x-ads.slot slot="home_between_cards" className="col-span-full" />
                @endif
                <a href="{{ $beach['url'] }}"
                   data-beach-id="{{ $beach['id'] }}"
                   @mouseenter="hoverBeach(@js($beach))"
                   wire:key="beach-{{ $beach['id'] }}"
                   class="glass-card card-lift shrink-0 p-4 rounded-3xl border transition-all duration-300 flex flex-col gap-3.5 group active:scale-[0.99] relative overflow-hidden"
                   :class="isFav({{ $beach['id'] }}) ? 'border-amber-500/30 bg-amber-500/[0.03] shadow-[inset_0_0_16px_rgba(245,158,11,0.03)]' : 'border-theme-medium hover:border-blue-500/40'">
                    
                    <div x-cloak x-show="isFav({{ $beach['id'] }})" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute top-0 left-0 w-1.5 h-full bg-gradient-to-b from-amber-400 to-yellow-500 shadow-md shadow-amber-500/30"></div>

                    <div class="flex items-start justify-between gap-2.5 relative z-10">
                        <div class="min-w-0 flex-1">
                            <h2 class="font-extrabold text-base sm:text-lg text-theme group-hover:text-blue-400 transition-colors truncate tracking-tight">
                                {{ $beach['name'] }}
                            </h2>
                            <div class="flex items-center gap-1 mt-0.5 text-xs text-slate-400 font-medium">
                                <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <circle cx="12" cy="11" r="2" stroke="currentColor" stroke-width="2"/>
                                </svg>
                                <span class="truncate">{{ $beach['municipality'] }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button"
                                    @click.prevent.stop="
                                        $el.querySelector('.fav-star').classList.add('animate-fav-bounce');
                                        setTimeout(() => $el.querySelector('.fav-star').classList.remove('animate-fav-bounce'), 500);
                                        toggleFav({{ $beach['id'] }});
                                    "
                                    class="p-2 touch-target rounded-xl transition-all hover:scale-125 active:scale-90 hover:bg-white/5"
                                    :class="isFav({{ $beach['id'] }}) ? 'opacity-100 drop-shadow-[0_0_8px_rgba(245,158,11,0.6)] text-amber-400' : 'opacity-30 hover:opacity-80 text-slate-400'"
                                    :title="isFav({{ $beach['id'] }}) ? '{{ __('common.favorite_remove') }}' : '{{ __('common.favorite_add') }}'">
                                    <span class="fav-star block text-lg">★</span>
                            </button>

                            @if($beach['flag'] === 'green')
                                <span class="bg-emerald-500/10 border border-emerald-500/25 text-emerald-400 font-bold px-3 py-1 rounded-full text-xs leading-none flex items-center gap-1.5 shadow-sm shadow-emerald-500/5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>{{ __('common.flag_green') }}</span>
                                </span>
                            @elseif($beach['flag'] === 'yellow')
                                <span class="bg-amber-500/10 border border-amber-500/25 text-amber-400 font-bold px-3 py-1 rounded-full text-xs leading-none flex items-center gap-1.5 shadow-sm shadow-amber-500/5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    <span>{{ __('common.flag_yellow') }}</span>
                                </span>
                            @elseif($beach['flag'] === 'red')
                                <span class="bg-rose-500/10 border border-rose-500/25 text-rose-400 font-bold px-3 py-1 rounded-full text-xs leading-none flex items-center gap-1.5 shadow-sm shadow-rose-500/5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                    <span>{{ __('common.flag_red') }}</span>
                                </span>
                            @elseif($beach['flag'] === 'blue_or_neutral')
                                <span class="bg-blue-500/10 border border-blue-500/25 text-blue-400 font-bold px-3 py-1 rounded-full text-xs leading-none flex items-center gap-1.5 shadow-sm shadow-blue-500/5">
                                    <span>❄️</span>
                                    <span>{{ __('common.flag_blue_or_neutral') }}</span>
                                </span>
                            @else
                                <span class="bg-slate-500/10 border border-slate-500/25 text-slate-400 font-bold px-3 py-1 rounded-full text-xs leading-none flex items-center gap-1.5">
                                    <span class="text-xs">—</span>
                                    <span>{{ __('common.flag_none') }}</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-1.5 relative z-10">
                        @if($beach['blue_flag'])
                            <span class="bg-blue-600/15 border border-blue-500/20 text-blue-400 text-[11px] px-2 py-0.5 rounded-md font-bold uppercase tracking-wider">
                                {{ __('common.flag_blue_flag') }}
                            </span>
                        @endif
                        @if($beach['accessible'])
                            <span class="bg-teal-600/15 border border-teal-500/20 text-teal-400 text-[11px] px-2 py-0.5 rounded-md font-bold uppercase tracking-wider">
                                {{ __('common.flag_accessible') }}
                            </span>
                        @endif
                        @if($beach['beachcam_slug'])
                            <span class="bg-amber-600/15 border border-amber-500/20 text-amber-400 text-[11px] px-2 py-0.5 rounded-md font-bold uppercase tracking-wider">
                                📹 {{ __('common.flag_webcam') }}
                            </span>
                        @endif

                        @if(in_array($beach['source'], ['report', 'consensus', 'community']))
                            <span class="bg-emerald-600/15 border border-emerald-500/25 text-emerald-400 text-[11px] px-2.5 py-0.5 rounded-md font-bold uppercase tracking-wider flex items-center gap-1 ml-auto shrink-0 shadow-sm shadow-emerald-500/5">
                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ __('common.flag_confirmed') }}
                            </span>
                        @else
                            <span class="bg-blue-600/10 border border-blue-500/10 text-blue-400/80 text-[11px] px-2.5 py-0.5 rounded-md font-medium uppercase tracking-wider flex items-center gap-1 ml-auto shrink-0">
                                {{ __('common.flag_prediction') }}
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-4 gap-1 pt-3 border-t border-theme-subtle/20 relative z-10">

                        <div class="flex flex-col items-center justify-center text-center" title="{{ __('common.weather_air') }}">
                            <div class="flex items-center gap-1 opacity-75">
                                <svg class="w-3.5 h-3.5 text-amber-500/80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m12.728 12.728l-.707.707"/>
                                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5" fill="none"/>
                                </svg>
                                <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">{{ __('common.weather_air') }}</span>
                            </div>
                            <span class="text-xs sm:text-sm font-bold text-slate-300 mt-1.5 tabular-nums">
                                {{ $beach['air_temp'] !== null ? $beach['air_temp'] . '°' : '—' }}
                            </span>
                        </div>

                        <div class="flex flex-col items-center justify-center text-center" title="{{ __('common.weather_water') }}">
                            <div class="flex items-center gap-1 opacity-75">
                                <svg class="w-3.5 h-3.5 text-blue-400/80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>
                                </svg>
                                <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">{{ __('common.weather_water') }}</span>
                            </div>
                            <span class="text-xs sm:text-sm font-bold text-slate-300 mt-1.5 tabular-nums">
                                {{ $beach['water_temp'] !== null ? $beach['water_temp'] . '°' : '—' }}
                            </span>
                        </div>

                        <div class="flex flex-col items-center justify-center text-center" title="{{ __('common.weather_waves') }}">
                            <div class="flex items-center gap-1 opacity-75">
                                <svg class="w-3.5 h-3.5 text-teal-400/80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2 12c4-4 8 4 12 0s8-4 12 0M2 17c4-4 8 4 12 0s8-4 12 0"/>
                                </svg>
                                <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">{{ __('common.weather_waves') }}</span>
                            </div>
                            <span class="text-xs sm:text-sm font-bold text-slate-300 mt-1.5 tabular-nums">
                                {{ $beach['wave_height_max'] !== null ? $beach['wave_height_max'] . 'm' : '—' }}
                            </span>
                        </div>

                        <div class="flex flex-col items-center justify-center text-center" title="{{ __('common.weather_wind') }}">
                            <div class="flex items-center gap-1 opacity-75">
                                <svg class="w-3.5 h-3.5 text-slate-400/80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7H4M16 11H8M18 15H6M19 19H9"/>
                                </svg>
                                <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">{{ __('common.weather_wind') }}</span>
                            </div>
                            <span class="text-xs sm:text-sm font-bold text-slate-300 mt-1.5 flex items-center justify-center gap-1 tabular-nums">
                                <span>{{ $beach['wind_speed'] !== null ? (int)round($beach['wind_speed'] * 1.852) : '—' }}</span>
                                @if($beach['wind_direction'] !== null)
                                    <span class="text-[11px] font-extrabold text-slate-500 uppercase">{{ $beach['wind_direction'] }}</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="glass-card p-6 sm:p-8 rounded-xl border border-theme-medium text-center text-theme-secondary animate-fade-in">
                    <p class="font-medium text-base sm:text-lg mb-1">{{ __('common.no_results_title') }}</p>
                    <p class="text-sm text-theme-muted">{{ __('common.no_results_hint') }}</p>
                </div>
            @endforelse

            @if($hasMore)
                <div wire:key="load-more-sentinel" id="infinite-scroll-sentinel"
                     x-data="{
                         observer: null,
                         init() {
                             if (!window._cpScroll) window._cpScroll = { loading: false, scrollPos: 0 };
                             const sentinel = this.$el;
                             const rootEl = document.getElementById('beach-scroll-container');
                             const useRoot = rootEl && getComputedStyle(rootEl).overflowY !== 'visible';

                             const load = () => {
                                 if (window._cpScroll.loading || !$wire.hasMore) return;
                                 window._cpScroll.loading = true;
                                 const container = useRoot ? rootEl : window;
                                 window._cpScroll.scrollPos = useRoot ? rootEl.scrollTop : window.scrollY;
                                 $wire.loadMore().then(() => {
                                     window._cpScroll.loading = false;
                                     requestAnimationFrame(() => {
                                         if (useRoot) rootEl.scrollTop = window._cpScroll.scrollPos;
                                         else window.scrollTo(0, window._cpScroll.scrollPos);
                                     });
                                 });
                             };

                             this.observer = new IntersectionObserver((entries) => {
                                 if (entries[0].isIntersecting) load();
                             }, {
                                 root: useRoot ? rootEl : null,
                                 rootMargin: useRoot ? '0px' : '600px',
                                 threshold: 0
                             });
                             this.observer.observe(sentinel);
                         },
                         destroy() { this.observer?.disconnect(); }
                     }"
                     class="flex justify-center py-4">
                    <div wire:loading class="flex items-center gap-2 text-theme-muted text-sm">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <span>{{ __('common.loading') ?? 'A carregar...' }}</span>
                    </div>
                </div>
            @endif
            </div><!-- end card list -->

            <x-ads.slot slot="home_bottom" className="col-span-full" />
        </div>

        <!-- Right Side: Maps -->
        <div wire:ignore class="lg:col-span-7 flex flex-col gap-2 sm:gap-3 min-h-[300px] sm:min-h-[400px] lg:min-h-full" :class="viewState === 'map' ? 'flex' : 'hidden lg:flex'">

            <!-- Main Map (holds whichever region is primary) -->
            <div id="map-primary-slot" class="flex-1 rounded-xl sm:rounded-2xl overflow-hidden border border-theme-medium relative min-h-[240px] sm:min-h-[280px] transition-all duration-300">
                <!-- Loading skeleton shown until first tile loads -->
                <div id="map-continente-skeleton" class="absolute inset-0 z-10 flex items-center justify-center bg-slate-900/60" aria-hidden="true">
                    <div class="flex flex-col items-center gap-2 text-slate-500">
                        <svg class="w-8 h-8 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        <span class="text-xs font-medium">{{ __('common.loading') ?? 'A carregar...' }}</span>
                    </div>
                </div>
                <div id="map-continente" class="w-full h-full absolute inset-0 z-0" role="application" aria-label="{{ __('home.map_continent_label') }}"></div>
            </div>

            <x-ads.slot slot="home_sidebar_bottom" />

            <!-- Island Maps Row (clickable to swap to main) -->
            <div class="grid grid-cols-2 gap-2 sm:gap-3 h-28 sm:h-36 shrink-0">
                <div id="slot-acores" class="rounded-lg sm:rounded-xl overflow-hidden border border-theme-medium relative group">
                    <div id="map-acores" class="w-full h-full absolute inset-0 z-0" role="application" aria-label="{{ __('home.map_azores_label') }}"></div>
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent pt-5 pb-1.5 px-2.5 z-10 pointer-events-none" aria-hidden="true">
                        <span class="text-white/90 text-xs sm:text-sm font-bold tracking-widest uppercase" x-text="slotLabel('acores')"></span>
                    </div>
                    <div class="absolute top-1.5 right-1.5 z-10 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none" aria-hidden="true">
                        <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                    </div>
                    <div @click="swapMaps(slotRegion('acores'))" class="absolute inset-0 z-20 cursor-pointer hover:bg-blue-500/10 active:bg-blue-500/20 transition-colors duration-150 rounded-lg sm:rounded-xl"></div>
                </div>
                <div id="slot-madeira" class="rounded-lg sm:rounded-xl overflow-hidden border border-theme-medium relative group">
                    <div id="map-madeira" class="w-full h-full absolute inset-0 z-0" role="application" aria-label="{{ __('home.map_madeira_label') }}"></div>
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent pt-5 pb-1.5 px-2.5 z-10 pointer-events-none" aria-hidden="true">
                        <span class="text-white/90 text-xs sm:text-sm font-bold tracking-widest uppercase" x-text="slotLabel('madeira')"></span>
                    </div>
                    <div class="absolute top-1.5 right-1.5 z-10 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none" aria-hidden="true">
                        <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                    </div>
                    <div @click="swapMaps(slotRegion('madeira'))" class="absolute inset-0 z-20 cursor-pointer hover:bg-blue-500/10 active:bg-blue-500/20 transition-colors duration-150 rounded-lg sm:rounded-xl"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating View Toggle Button -->
    <div class="fixed bottom-20 sm:bottom-24 left-1/2 -translate-x-1/2 z-40 md:hidden pb-safe">
        <button @click="
                viewState = (viewState === 'map' ? 'list' : 'map');
                if (viewState === 'map') {
                    window.dispatchEvent(new Event('checkpraia:map-visible'));
                    setTimeout(() => invalidateAllMaps(), 150);
                }" 
                class="bg-blue-600 hover:bg-blue-500 active:scale-90 text-white font-bold px-5 py-3.5 rounded-full shadow-lg shadow-blue-500/25 flex items-center gap-2 text-sm uppercase tracking-wider touch-target transition-all backdrop-blur-sm bg-blue-600/90 hover:shadow-xl hover:shadow-blue-500/30">
            <svg x-show="viewState === 'map'" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            <svg x-show="viewState === 'list'" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
            </svg>
            <span x-text="viewState === 'map' ? '{{ __('common.view_list') }}' : '{{ __('common.view_map') }}'" class="transition-opacity"></span>
        </button>
    </div>

    <!-- Scroll to Top Button -->
    <div x-data="{ visible: false }" x-init="window.addEventListener('scroll', () => { visible = window.scrollY > 600; }, { passive: true })" class="fixed bottom-32 right-4 sm:right-6 z-40">
        <button x-show="visible" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 scale-75" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-4 scale-75" @click="window.scrollTo({ top: 0, behavior: 'smooth' })" class="bg-blue-600/90 hover:bg-blue-500 active:scale-90 text-white w-11 h-11 rounded-full shadow-lg shadow-blue-500/25 flex items-center justify-center transition-all backdrop-blur-sm touch-target" aria-label="Scroll to top">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
        </button>
    </div>

    @script
    <script>
        Alpine.data('cardFavorites', (beaches) => ({
            favs: {},
            init() {
                for (const b of beaches) {
                    if (b.is_favorited) this.favs[b.id] = true;
                }
            },
            isFav(id) { return !!this.favs[id]; },
            _reorderCards(container) {
                const cards = [...container.children].filter(c => c.hasAttribute('data-beach-id'));
                const favCards = [];
                const nonFavCards = [];
                for (const c of cards) {
                    (this.favs[c.dataset.beachId] ? favCards : nonFavCards).push(c);
                }
                const sortByName = (a, b) => (a.querySelector('h2')?.textContent || '').trim().localeCompare((b.querySelector('h2')?.textContent || '').trim());
                favCards.sort(sortByName);
                nonFavCards.sort(sortByName);
                for (const card of [...favCards, ...nonFavCards]) container.appendChild(card);
            },
            toggleFav(id) {
                this.favs[id] = !this.favs[id];
                const container = this.$refs.beachList;
                if (container) this.$nextTick(() => this._reorderCards(container));

                fetch('{{ route('favorites.toggle') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ beach_id: id })
                }).then(r => {
                    if (!r.ok) return r.json().then(d => { throw new Error(d.error || 'Error'); });
                }).catch(err => {
                    this.favs[id] = !this.favs[id];
                    if (container) this.$nextTick(() => this._reorderCards(container));
                    window.dispatchEvent(new CustomEvent('favorite-error', { detail: { message: err.message || '{{ __('common.favorite_login_required') }}' } }));
                });
            }
        }));

        Alpine.data('beachMapHandler', (initialBeaches, serverDefaultRegion) => ({
            mapContinente: null,
            mapAcores: null,
            mapMadeira: null,
            markers: { continente: {}, acores: {}, madeira: {} },
            beaches: initialBeaches,
            viewState: window.innerWidth < 1024 ? 'list' : 'map',
            userCircle: null,
            tileLayers: { continente: null, acores: null, madeira: null },
            nearby: [],
            locationError: null,
            locationErrorTimer: null,
            _initialRender: true,
            activeRegion: 'Continental',
            _defaultRegion: serverDefaultRegion,
            _swapPending: null,
            _tileFallbackAttempted: { continente: false, acores: false, madeira: false },
            _skeletonTimeout: null,

            init() {
                const isMobile = window.innerWidth < 1024;

                if (isMobile) {
                    // On mobile the map starts hidden (display:none via Alpine x-show).
                    // IntersectionObserver can't observe display:none — instead, the toggle
                    // button dispatches 'checkpraia:map-visible' when the user switches to map view.
                    const handler = () => {
                        window.removeEventListener('checkpraia:map-visible', handler);
                        this._waitForLeafletAndInit();
                    };
                    window.addEventListener('checkpraia:map-visible', handler);
                } else {
                    // Desktop: map is always visible — initialise as soon as Leaflet loads.
                    this._waitForLeafletAndInit();
                }
            },

            _waitForLeafletAndInit() {
                if (typeof L !== 'undefined') {
                    this._initMaps();
                    return;
                }
                // Script is loading (defer) — poll until ready (max 20s)
                let attempts = 0;
                const check = setInterval(() => {
                    attempts++;
                    if (typeof L !== 'undefined') {
                        clearInterval(check);
                        this._initMaps();
                    } else if (attempts > 400) {
                        clearInterval(check); // Timeout safety — 20s
                        this._initMapsFailed();
                    }
                }, 50);
            },

            _initMapsFailed() {
                // Leaflet never loaded — remove skeleton so user isn't stuck
                const sk = document.getElementById('map-continente-skeleton');
                if (sk) { sk.style.transition = 'opacity 0.3s'; sk.style.opacity = '0'; setTimeout(() => sk.remove(), 300); }
                console.warn('CheckPraia: Leaflet did not load within timeout');
            },

            _initMaps() {
                const contEl = document.getElementById('map-continente');
                if (!contEl) return;

                // Reuse existing map if Leaflet already initialized on this container
                if (contEl.__leafletMap) {
                    this.mapContinente = contEl.__leafletMap;
                    this.mapAcores = document.getElementById('map-acores')?.__leafletMap || null;
                    this.mapMadeira = document.getElementById('map-madeira')?.__leafletMap || null;
                    this.renderMarkers();
                    this.invalidateAllMaps();
                    this._initialRender = false;
                    this._applyInitialRegion();
                    return;
                }

                const removePopupHref = (e) => {
                    const closeBtn = e.popup._container.querySelector('.leaflet-popup-close-button');
                    if (closeBtn) {
                        closeBtn.removeAttribute('href');
                        closeBtn.setAttribute('role', 'button');
                    }
                };

                const fixZoomControlLinks = (el) => {
                    setTimeout(() => {
                        el.querySelectorAll('.leaflet-control-zoom a').forEach(a => {
                            if (a.getAttribute('href') === '#') {
                                a.removeAttribute('href');
                                a.setAttribute('role', 'button');
                                a.setAttribute('tabindex', '0');
                            }
                        });
                    }, 200);
                };

                // Init main continental map — only when Leaflet is ready
                this.mapContinente = L.map('map-continente', {
                    zoomControl: true,
                    maxZoom: 18,
                    minZoom: 6
                }).setView([39.6, -8.2], 7);
                this.mapContinente.on('popupopen', removePopupHref);
                contEl.__leafletMap = this.mapContinente;
                fixZoomControlLinks(contEl);

                this.tileLayers.continente = this.createTileLayer();
                this.tileLayers.continente.addTo(this.mapContinente);

                // Hide skeleton once first tile loads
                this.tileLayers.continente.once('tileload', () => {
                    this._hideSkeleton();
                });

                // Fail-safe: hide skeleton after 15s even if tiles never arrive
                this._skeletonTimeout = setTimeout(() => {
                    this._hideSkeleton();
                }, 15000);

                // Handle tile errors — try fallback
                this.tileLayers.continente.on('tileerror', () => {
                    this._tryFallbackTile('continente', this.mapContinente);
                });

                // Island maps: defer via requestIdleCallback to keep main thread free
                const initIslands = () => {
                    this.lazyLoadMap('map-acores', 'acores', [[36.7, -28.9], [38.8, -24.7]], removePopupHref);
                    this.lazyLoadMap('map-madeira', 'madeira', [[32.4, -17.4], [33.3, -16.1]], removePopupHref);
                };

                if ('requestIdleCallback' in window) {
                    requestIdleCallback(initIslands, { timeout: 3000 });
                } else {
                    setTimeout(initIslands, 800);
                }

                this.renderMarkers();
                this._initialRender = false;

                this._applyInitialRegion();

                window.addEventListener('nearby-updated', (event) => {
                    this.nearby = event.detail.nearby;
                });

                window.addEventListener('beaches-updated', (event) => {
                    this.beaches = event.detail.beaches;
                    this.invalidateAllMaps();
                    this.renderMarkers();
                    this._fitBeaches();
                });

            },

            lazyLoadMap(elementId, region, bounds, removePopupHref) {
                const el = document.getElementById(elementId);
                if (!el) return;
                if (window.innerWidth < 1024 && 'IntersectionObserver' in window) {
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                this.initIslandMap(elementId, region, bounds, removePopupHref);
                                observer.disconnect();
                            }
                        });
                    }, { rootMargin: '200px' });
                    observer.observe(el);
                } else {
                    this.initIslandMap(elementId, region, bounds, removePopupHref);
                }
            },

            initIslandMap(elementId, region, bounds, removePopupHref) {
                const el = document.getElementById(elementId);
                if (!el) return;

                // Reuse existing island map if already initialized
                if (el.__leafletMap) {
                    const map = el.__leafletMap;
                    this['map' + region.charAt(0).toUpperCase() + region.slice(1)] = map;
                    this.renderRegionMarkers(map, this.markers[region], this.beaches.filter(b => {
                        const r = b.region || 'Continental';
                        if (region === 'acores') return r === 'Açores';
                        if (region === 'madeira') return r === 'Madeira';
                        return false;
                    }));
                    setTimeout(() => {
                        map.invalidateSize();
                        if (this._swapPending) {
                            const pendingRegion = this._swapPending;
                            this._swapPending = null;
                            this._performSwap(pendingRegion);
                        }
                    }, 100);
                    return;
                }

                const center = bounds.reduce((acc, b) => [acc[0] + b[0]/2, acc[1] + b[1]/2], [0,0]);
                const map = L.map(elementId, {
                    zoomControl: false,
                    attributionControl: false,
                    maxZoom: 18,
                    minZoom: 6,
                    dragging: true,
                    scrollWheelZoom: true
                }).setView(center, 7);
                map.on('popupopen', removePopupHref);
                el.__leafletMap = map;
                const layer = this.createTileLayer();
                layer.addTo(map);
                layer.on('tileerror', () => {
                    this._tryFallbackTile(region, map);
                });
                this['map' + region.charAt(0).toUpperCase() + region.slice(1)] = map;
                this.tileLayers[region] = layer;
                this.renderRegionMarkers(map, this.markers[region], this.beaches.filter(b => {
                    const r = b.region || 'Continental';
                    if (region === 'acores') return r === 'Açores';
                    if (region === 'madeira') return r === 'Madeira';
                    return false;
                }));
                setTimeout(() => {
                    map.invalidateSize();
                    if (this._swapPending) {
                        const pendingRegion = this._swapPending;
                        this._swapPending = null;
                        this._performSwap(pendingRegion);
                    }
                }, 100);
            },

            _hideSkeleton() {
                if (this._skeletonTimeout) {
                    clearTimeout(this._skeletonTimeout);
                    this._skeletonTimeout = null;
                }
                const sk = document.getElementById('map-continente-skeleton');
                if (sk) { sk.style.transition = 'opacity 0.3s'; sk.style.opacity = '0'; setTimeout(() => sk.remove(), 300); }
            },

            _tryFallbackTile(region, map) {
                const key = region || 'continente';
                if (this._tileFallbackAttempted[key]) return;
                this._tileFallbackAttempted[key] = true;

                // Remove failed CARTO layer; fallback to OSM
                if (this.tileLayers[key]) {
                    map.removeLayer(this.tileLayers[key]);
                }
                const fallback = L.tileLayer(
                    'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                    {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                        maxZoom: 19,
                        keepBuffer: 1,
                        updateWhenIdle: true,
                        updateWhenZooming: false,
                    }
                );
                fallback.addTo(map);
                this.tileLayers[key] = fallback;
                this._hideSkeleton();
            },

            createTileLayer() {
                // CARTO Positron — lightweight vector-look tiles (~4-8 KB/tile vs ~100 KB for satellite).
                // Works well on both mobile and desktop. Fast CDN with sub-domains for parallelism.
                const layer = L.tileLayer(
                    'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png',
                    {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> &copy; <a href="https://carto.com/">CARTO</a>',
                        subdomains: 'abcd',
                        maxZoom: 19,
                        alt: 'Mapa',
                        // Fetch only tiles needed for viewport to reduce bandwidth
                        keepBuffer: 1,
                        updateWhenIdle: true,
                        updateWhenZooming: false,
                    }
                );
                let tileCounter = 0;
                layer.on('tileload', function(e) {
                    if (e.tile) {
                        tileCounter++;
                        e.tile.setAttribute('role', 'presentation');
                        e.tile.setAttribute('aria-hidden', 'true');
                        e.tile.setAttribute('alt', `Mapa Bloco ${tileCounter}`);
                    }
                });
                return layer;
            },

            renderMarkers() {
                this.clearMarkers();

                const byRegion = { Continental: [], Madeira: [], Açores: [] };
                this.beaches.forEach(beach => {
                    const r = beach.region || 'Continental';
                    if (byRegion[r]) byRegion[r].push(beach);
                    else byRegion.Continental.push(beach);
                });

                this.renderRegionMarkers(this.mapContinente, this.markers.continente, byRegion.Continental);
                this.renderRegionMarkers(this.mapAcores, this.markers.acores, byRegion.Açores);
                this.renderRegionMarkers(this.mapMadeira, this.markers.madeira, byRegion.Madeira);
            },

            renderRegionMarkers(map, store, beaches) {
                if (!map) return;
                beaches.forEach(beach => {
                    const color = this.getMarkerColor(beach.flag);
                    const size = window.innerWidth < 640 ? 12 : 14;
                    const icon = L.divIcon({
                        className: 'custom-div-icon',
                        html: `<div style="width:${size}px;height:${size}px;background:${color};border:2px solid var(--marker-border, #070a13);border-radius:50%;box-shadow:0 0 10px ${color}80;"></div>`,
                        iconSize: [size, size],
                        iconAnchor: [size/2, size/2]
                    });
                    const marker = L.marker([beach.latitude, beach.longitude], { icon })
                        .addTo(map)
                        .bindPopup(`
                            <div class="beach-popup-inner">
                                <div style="display:flex;align-items:center;gap:8px;">
                                     <span style="width:10px;height:10px;border-radius:50%;background:${color};display:inline-block;flex-shrink:0;box-shadow:0 0 6px ${color};"></span>
                                    <span class="beach-popup-name">${beach.name}</span>
                                </div>
                                <div class="beach-popup-location">📍 ${beach.municipality}</div>
                                        <a href="${beach.url}" class="beach-popup-btn">{{ __('home.beach_detail_link') }}</a>
                            </div>
                        `, { className: 'beach-popup', closeButton: false, maxWidth: 260, minWidth: 160 });
                    
                    const markerEl = marker.getElement();
                    if (markerEl) {
                        markerEl.setAttribute('aria-label', `{{ __('home.marker_label', ['name' => '']) }}${beach.name}`);
                    }
                    store[beach.id] = marker;
                });
                if (beaches.length && !this._initialRender) {
                    const coords = beaches.map(b => [b.latitude, b.longitude]);
                    map.fitBounds(coords, { padding: [10, 10], maxZoom: 12 });
                }
            },

            clearMarkers() {
                ['continente', 'acores', 'madeira'].forEach(key => {
                    const map = key === 'continente' ? this.mapContinente : key === 'acores' ? this.mapAcores : this.mapMadeira;
                    if (map) {
                        map.eachLayer((layer) => {
                            if (layer instanceof L.Marker) map.removeLayer(layer);
                        });
                    }
                    this.markers[key] = {};
                });
            },

            getMarkerColor(flag) {
                switch (flag) {
                    case 'green': return '#10b981';
                    case 'yellow': return '#f59e0b';
                    case 'red': return '#ef4444';
                    case 'blue_or_neutral': return '#3b82f6';
                    default: return '#6b7280';
                }
            },

            hoverBeach(beach) {
                const r = beach.region || 'Continental';
                let map, store;
                if (r === 'Açores') {
                    if (!this.mapAcores) this.initIslandMap('map-acores', 'acores', [[36.7, -28.9], [38.8, -24.7]], () => {});
                    map = this.mapAcores; store = this.markers.acores;
                } else if (r === 'Madeira') {
                    if (!this.mapMadeira) this.initIslandMap('map-madeira', 'madeira', [[32.4, -17.4], [33.3, -16.1]], () => {});
                    map = this.mapMadeira; store = this.markers.madeira;
                } else { map = this.mapContinente; store = this.markers.continente; }
                const marker = store[beach.id];
                if (marker) {
                    map.panTo([beach.latitude, beach.longitude]);
                    marker.openPopup();
                }
            },

            locateUser(auto = false) {
                if (this.locationErrorTimer) clearTimeout(this.locationErrorTimer);
                this.locationError = null;

                if (this.userCircle) {
                    if (this.mapContinente) this.mapContinente.removeLayer(this.userCircle);
                    if (this.mapAcores) this.mapAcores.removeLayer(this.userCircle);
                    if (this.mapMadeira) this.mapMadeira.removeLayer(this.userCircle);
                }

                window.CheckPraiaPermissions.requestLocation('home_nearby').then((position) => {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    const acc = position.coords.accuracy;

                    $wire.set('latitude', lat);
                    $wire.set('longitude', lon);
                    $wire.call('findNearby');

                    let map = this.mapContinente;
                    if (lat > 36 && lat < 40 && lon > -32 && lon < -24) {
                        map = this.mapAcores;
                    } else if (lat > 32 && lat < 33.5 && lon > -17.5 && lon < -16) {
                        map = this.mapMadeira;
                    }

                    map.setView([lat, lon], 12);
                    this.userCircle = L.circle([lat, lon], {
                        color: '#3b82f6',
                        fillColor: '#93c5fd',
                        fillOpacity: 0.25,
                        radius: Math.max(acc, 50)
                    }).addTo(map).bindPopup(`
                        {{ __('common.gps_your_location') }}<br>
                        <span class="text-[11px] text-slate-400">{{ __('common.gps_accuracy') }}: ${Math.round(acc)}m</span>
                    `).openPopup();
                }).catch((err) => {
                    if (auto) return;
                    const msgs = {
                        'GPS_NOT_SUPPORTED': '{{ __('common.gps_not_supported') }}',
                        'GPS_DENIED': '{{ __('common.gps_denied') }}',
                        'GPS_UNAVAILABLE': '{{ __('common.gps_unavailable') }}',
                        'GPS_TIMEOUT': '{{ __('common.gps_timeout') }}',
                        'GPS_ERROR': '{{ __('common.gps_error') }}',
                    };
                    this.locationError = msgs[err.message] || '{{ __('common.gps_error') }}';
                    this.locationErrorTimer = setTimeout(() => this.locationError = null, 5000);
                });
            },

            invalidateAllMaps() {
                if (this.mapContinente) this.mapContinente.invalidateSize();
                if (this.mapAcores) this.mapAcores.invalidateSize();
                if (this.mapMadeira) this.mapMadeira.invalidateSize();
            },

            _fitBeaches() {
                const byRegion = { Continental: [], Madeira: [], Açores: [] };
                this.beaches.forEach(beach => {
                    const r = beach.region || 'Continental';
                    if (byRegion[r]) byRegion[r].push(beach);
                    else byRegion.Continental.push(beach);
                });
                if (byRegion.Continental.length && this.mapContinente) {
                    this.mapContinente.fitBounds(byRegion.Continental.map(b => [b.latitude, b.longitude]), { padding: [10, 10], maxZoom: 12 });
                }
                if (byRegion.Açores.length && this.mapAcores) {
                    this.mapAcores.fitBounds(byRegion.Açores.map(b => [b.latitude, b.longitude]), { padding: [10, 10], maxZoom: 12 });
                }
                if (byRegion.Madeira.length && this.mapMadeira) {
                    this.mapMadeira.fitBounds(byRegion.Madeira.map(b => [b.latitude, b.longitude]), { padding: [10, 10], maxZoom: 12 });
                }
            },

            _applyInitialRegion() {
                let region = this._defaultRegion;
                if (!region) {
                    try { region = localStorage.getItem('checkpraia-active-region'); } catch (e) {}
                }
                if (!region || region === 'Continental') {
                    try { localStorage.removeItem('checkpraia-active-region'); } catch (e) {}
                    return;
                }

                const regionMap = { 'Açores': 'map-acores', 'Madeira': 'map-madeira' };
                const elId = regionMap[region];
                if (!elId) return;

                const el = document.getElementById(elId);
                if (!el || !el.__leafletMap) {
                    this._swapPending = region;
                    return;
                }

                this._swapPending = null;
                this._performSwap(region);
            },

            swapMaps(region) {
                if (this.activeRegion === region) return;

                const regionMap = { 'Açores': 'map-acores', 'Madeira': 'map-madeira', 'Continental': 'map-continente' };
                const elId = regionMap[region];
                if (!elId) return;

                const el = document.getElementById(elId);
                if (!el || !el.__leafletMap) {
                    if (region === 'Continental') {
                        this._performSwap(region);
                        try { localStorage.setItem('checkpraia-active-region', region); } catch (e) {}
                        if (typeof $wire !== 'undefined') $wire.call('saveDefaultRegion', region);
                        return;
                    }
                    const removePopupHref = (e) => {
                        const closeBtn = e.popup._container.querySelector('.leaflet-popup-close-button');
                        if (closeBtn) { closeBtn.removeAttribute('href'); closeBtn.setAttribute('role', 'button'); }
                    };
                    const bounds = region === 'Açores' ? [[36.7, -28.9], [38.8, -24.7]] : [[32.4, -17.4], [33.3, -16.1]];
                    this.initIslandMap(elId, region.charAt(0).toLowerCase() + region.slice(1), bounds, removePopupHref);
                }

                this._performSwap(region);

                try { localStorage.setItem('checkpraia-active-region', region); } catch (e) {}
                if (typeof $wire !== 'undefined') {
                    $wire.call('saveDefaultRegion', region);
                }
            },

            _performSwap(region) {
                this.activeRegion = region;

                const elContinente = document.getElementById('map-continente');
                const elAcores = document.getElementById('map-acores');
                const elMadeira = document.getElementById('map-madeira');
                const primarySlot = document.getElementById('map-primary-slot');
                const slotAcores = document.getElementById('slot-acores');
                const slotMadeira = document.getElementById('slot-madeira');

                [elContinente, elAcores, elMadeira].forEach(el => {
                    if (el && el.parentElement) el.parentElement.removeChild(el);
                });

                const allEls = { 'Continental': elContinente, 'Açores': elAcores, 'Madeira': elMadeira };
                if (allEls[region]) primarySlot.appendChild(allEls[region]);

                const smallSlots = [slotAcores, slotMadeira];
                let slotIdx = 0;
                ['Continental', 'Açores', 'Madeira'].forEach(r => {
                    if (r !== region && slotIdx < smallSlots.length) {
                        smallSlots[slotIdx].appendChild(allEls[r]);
                        slotIdx++;
                    }
                });

                setTimeout(() => {
                    if (this.mapContinente) this.mapContinente.invalidateSize();
                    if (this.mapAcores) this.mapAcores.invalidateSize();
                    if (this.mapMadeira) this.mapMadeira.invalidateSize();
                }, 50);
            },

            slotLabel(slotId) {
                const labels = { Continental: 'Continental', 'Açores': 'Açores', Madeira: 'Madeira' };
                const remaining = ['Continental', 'Açores', 'Madeira'].filter(r => r !== this.activeRegion);
                const slotIndex = slotId === 'acores' ? 0 : 1;
                return labels[remaining[slotIndex]] || '';
            },

            slotRegion(slotId) {
                const remaining = ['Continental', 'Açores', 'Madeira'].filter(r => r !== this.activeRegion);
                return remaining[slotId === 'acores' ? 0 : 1];
            }
        }));
    </script>
    @endscript
</div>
