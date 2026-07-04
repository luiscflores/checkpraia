<div class="space-y-3 sm:space-y-6" x-data="beachMapHandler(@js($mapBeaches))">
    @section('title', __('home.title'))

    <h1 class="sr-only">{{ __('home.page_title') }}</h1>

    @if(session()->has('favorite_success'))
        <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 text-xs rounded-xl font-medium animate-fade-in">
            {{ session('favorite_success') }}
        </div>
    @endif

    @if(session()->has('favorite_error'))
        <div class="p-3 bg-rose-500/10 border border-rose-500/20 text-rose-200 text-xs rounded-xl font-medium animate-fade-in">
            {{ session('favorite_error') }}
        </div>
    @endif

    <!-- Search and Filters Panel -->
    <div class="glass-card p-4 rounded-3xl border border-theme-subtle/50 space-y-4 shadow-lg shadow-black/[0.02] animate-fade-in-up" x-data="{ searchFocused: false }">
        <div class="flex items-stretch gap-2.5">
            <div class="w-full relative flex-1">
                <label for="beach-search" class="sr-only">{{ __('common.search_placeholder') }}</label>
                <input 
                    id="beach-search"
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="{{ __('common.search_placeholder') }}" 
                    @focus="searchFocused = true" @blur="searchFocused = false"
                    class="w-full bg-theme-input border border-theme-subtle/60 px-4 py-3.5 pl-11 pr-10 rounded-2xl text-base sm:text-sm text-theme placeholder:text-theme-muted focus:outline-none focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/10 transition-all shadow-inner"
                />
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4.5 h-4.5 text-theme-muted transition-colors" :class="searchFocused && 'text-blue-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                @if(strlen($search) > 0)
                    <button wire:click="$set('search', '')" class="absolute right-3 top-1/2 -translate-y-1/2 text-theme-muted hover:text-theme transition-colors p-1" aria-label="{{ __('common.search_clear') }}">
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
        </div>

        <!-- Flag Filters -->
        <div class="flex flex-wrap gap-2">
            <button wire:click="$set('selectedFlag', '')" 
                    class="basis-[30%] sm:basis-auto grow sm:grow-0 whitespace-nowrap px-3.5 py-2.5 rounded-full text-xs font-bold transition-all border touch-target min-h-[42px] flex items-center justify-center gap-1.5 {{ $selectedFlag === '' ? 'bg-blue-600/10 border-blue-500/30 text-blue-400 shadow-sm' : 'bg-theme-card border-theme-subtle text-theme-secondary hover:text-theme hover:border-theme-medium' }}">
                <span>🏁</span>
                <span>{{ __('common.flag_all') }}</span>
            </button>
            <button wire:click="$set('selectedFlag', 'green')" 
                    class="basis-[30%] sm:basis-auto grow sm:grow-0 whitespace-nowrap px-3.5 py-2.5 rounded-full text-xs font-bold transition-all border touch-target min-h-[42px] flex items-center justify-center gap-1.5 {{ $selectedFlag === 'green' ? 'bg-emerald-600/10 border-emerald-500/30 text-emerald-400 shadow-sm' : 'bg-theme-card border-theme-subtle text-theme-secondary hover:text-theme hover:border-theme-medium' }}">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <span>{{ __('common.flag_green') }}</span>
            </button>
            <button wire:click="$set('selectedFlag', 'yellow')" 
                    class="basis-[30%] sm:basis-auto grow sm:grow-0 whitespace-nowrap px-3.5 py-2.5 rounded-full text-xs font-bold transition-all border touch-target min-h-[42px] flex items-center justify-center gap-1.5 {{ $selectedFlag === 'yellow' ? 'bg-amber-600/10 border-amber-500/30 text-amber-400 shadow-sm' : 'bg-theme-card border-theme-subtle text-theme-secondary hover:text-theme hover:border-theme-medium' }}">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                <span>{{ __('common.flag_yellow') }}</span>
            </button>
            <button wire:click="$set('selectedFlag', 'red')" 
                    class="basis-[30%] sm:basis-auto grow sm:grow-0 whitespace-nowrap px-3.5 py-2.5 rounded-full text-xs font-bold transition-all border touch-target min-h-[42px] flex items-center justify-center gap-1.5 {{ $selectedFlag === 'red' ? 'bg-rose-600/10 border-rose-500/30 text-rose-400 shadow-sm' : 'bg-theme-card border-theme-subtle text-theme-secondary hover:text-theme hover:border-theme-medium' }}">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                <span>{{ __('common.flag_red') }}</span>
            </button>
            <button wire:click="$set('selectedFlag', 'blue_or_neutral')" 
                    class="basis-[30%] sm:basis-auto grow sm:grow-0 whitespace-nowrap px-3.5 py-2.5 rounded-full text-xs font-bold transition-all border touch-target min-h-[42px] flex items-center justify-center gap-1.5 {{ $selectedFlag === 'blue_or_neutral' ? 'bg-blue-600/10 border-blue-500/30 text-blue-400 shadow-sm' : 'bg-theme-card border-theme-subtle text-theme-secondary hover:text-theme hover:border-theme-medium' }}">
                <span>❄️</span>
                <span>{{ __('common.flag_blue_or_neutral') }}</span>
            </button>
        </div>
    </div>

    <!-- Split Content View -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 sm:gap-6 min-h-[450px] sm:min-h-[550px]">
        
        <!-- Left Side: Beach Cards List -->
        <div class="lg:col-span-5 flex flex-col gap-4.5 max-h-[calc(100vh-220px)] lg:max-h-[720px] overflow-y-auto pr-0.5 sm:pr-2 pb-6 scrollbar-thin" :class="viewState === 'list' ? 'flex' : 'hidden lg:flex'">
            @forelse($beachesList as $i => $beach)
                <a href="{{ $beach['url'] }}" 
                   @mouseenter="hoverBeach(@js($beach))"
                   data-animate
                   data-stagger-delay="{{ min($i * 0.03, 0.5) }}"
                   class="glass-card card-lift shrink-0 p-4 rounded-3xl border transition-all duration-300 flex flex-col gap-3.5 group active:scale-[0.99] relative overflow-hidden {{ $beach['is_favorited'] ? 'border-amber-500/30 bg-amber-500/[0.03] shadow-[inset_0_0_16px_rgba(245,158,11,0.03)]' : 'border-theme-medium hover:border-blue-500/40' }}">
                    
                    @if($beach['is_favorited'])
                        <div class="absolute top-0 left-0 w-1.5 h-full bg-gradient-to-b from-amber-400 to-yellow-500 shadow-md shadow-amber-500/30"></div>
                    @endif

                    <!-- Shimmer on hover -->
                    <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none">
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/[0.02] to-transparent animate-shimmer"></div>
                    </div>

                    <div class="flex items-start justify-between gap-2.5 relative z-10">
                        <div class="min-w-0 flex-1">
                            <h2 class="font-extrabold text-base sm:text-lg text-theme group-hover:text-blue-400 transition-colors truncate tracking-tight">{{ $beach['name'] }}</h2>
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
                                    @click.prevent.stop="$wire.toggleFavorite({{ $beach['id'] }})"
                                    class="p-1 rounded-xl text-lg transition-all hover:scale-125 active:scale-90 hover:bg-white/5 {{ $beach['is_favorited'] ? 'opacity-100 drop-shadow-[0_0_8px_rgba(245,158,11,0.6)] text-amber-400' : 'opacity-30 hover:opacity-80 text-slate-400' }}"
                                    title="{{ $beach['is_favorited'] ? __('common.favorite_remove') : __('common.favorite_add') }}">
                                ★
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
                            <span class="bg-blue-600/15 border border-blue-500/20 text-blue-400 text-[10px] px-2 py-0.5 rounded-md font-bold uppercase tracking-wider">
                                {{ __('common.flag_blue_flag') }}
                            </span>
                        @endif
                        @if($beach['accessible'])
                            <span class="bg-teal-600/15 border border-teal-500/20 text-teal-400 text-[10px] px-2 py-0.5 rounded-md font-bold uppercase tracking-wider">
                                {{ __('common.flag_accessible') }}
                            </span>
                        @endif

                        @if($beach['source'] === 'report' || $beach['source'] === 'consensus')
                            <span class="bg-emerald-600/15 border border-emerald-500/25 text-emerald-400 text-[10px] px-2.5 py-0.5 rounded-md font-bold uppercase tracking-wider flex items-center gap-1 ml-auto shrink-0 shadow-sm shadow-emerald-500/5">
                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ __('common.flag_confirmed') }}
                            </span>
                        @else
                            <span class="bg-blue-600/10 border border-blue-500/10 text-blue-400/80 text-[10px] px-2.5 py-0.5 rounded-md font-medium uppercase tracking-wider flex items-center gap-1 ml-auto shrink-0">
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
                                <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">{{ __('common.weather_air') }}</span>
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
                                <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">{{ __('common.weather_water') }}</span>
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
                                <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">{{ __('common.weather_waves') }}</span>
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
                                <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">{{ __('common.weather_wind') }}</span>
                            </div>
                            <span class="text-xs sm:text-sm font-bold text-slate-300 mt-1.5 flex items-center justify-center gap-1 tabular-nums">
                                <span>{{ $beach['wind_speed'] !== null ? (int)round($beach['wind_speed'] * 1.852) : '—' }}</span>
                                @if($beach['wind_direction'] !== null)
                                    <span class="text-[9px] font-extrabold text-slate-500 uppercase">{{ $beach['wind_direction'] }}</span>
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
        </div>

        <!-- Right Side: Maps -->
        <div wire:ignore class="lg:col-span-7 flex flex-col gap-2 sm:gap-3 min-h-[300px] sm:min-h-[400px] lg:min-h-full" :class="viewState === 'map' ? 'flex' : 'hidden lg:flex'">

            <!-- Main Map - Continental Portugal -->
            <div class="flex-1 rounded-xl sm:rounded-2xl overflow-hidden border border-theme-medium relative min-h-[240px] sm:min-h-[280px]">
                <div id="map-continente" class="w-full h-full absolute inset-0 z-0" role="application" aria-label="{{ __('home.map_continent_label') }}"></div>
            </div>

            <!-- Island Maps Row -->
            <div class="grid grid-cols-2 gap-2 sm:gap-3 h-28 sm:h-36 shrink-0">
                <div class="rounded-lg sm:rounded-xl overflow-hidden border border-theme-medium relative">
                    <div id="map-acores" class="w-full h-full absolute inset-0 z-0" role="application" aria-label="{{ __('home.map_azores_label') }}"></div>
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent pt-5 pb-1.5 px-2.5 z-10" aria-hidden="true">
                        <span class="text-white/90 text-xs sm:text-sm font-bold tracking-widest uppercase">{{ __('home.azores') }}</span>
                    </div>
                </div>
                <div class="rounded-lg sm:rounded-xl overflow-hidden border border-theme-medium relative">
                    <div id="map-madeira" class="w-full h-full absolute inset-0 z-0" role="application" aria-label="{{ __('home.map_madeira_label') }}"></div>
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent pt-5 pb-1.5 px-2.5 z-10" aria-hidden="true">
                        <span class="text-white/90 text-xs sm:text-sm font-bold tracking-widest uppercase">{{ __('home.madeira') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating View Toggle Button -->
    <div class="fixed bottom-20 sm:bottom-24 left-1/2 -translate-x-1/2 z-40 md:hidden pb-safe">
        <button @click="viewState = (viewState === 'map' ? 'list' : 'map'); setTimeout(() => { if (viewState === 'map') invalidateAllMaps(); }, 150);" 
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

    @script
    <script>
        Alpine.data('beachMapHandler', (initialBeaches) => ({
            mapContinente: null,
            mapAcores: null,
            mapMadeira: null,
            markers: { continente: {}, acores: {}, madeira: {} },
            beaches: initialBeaches,
            viewState: 'map',
            userCircle: null,
            tileLayers: { continente: null, acores: null, madeira: null },

            init() {
                if (this.mapContinente) return;

                const removePopupHref = (e) => {
                    const closeBtn = e.popup._container.querySelector('.leaflet-popup-close-button');
                    if (closeBtn) {
                        closeBtn.removeAttribute('href');
                        closeBtn.setAttribute('role', 'button');
                    }
                };

                this.mapContinente = L.map('map-continente', {
                    zoomControl: true,
                    maxZoom: 18,
                    minZoom: 6
                }).fitBounds([[36.95, -9.5], [42.15, -6.2]], { padding: [20, 20] });
                this.mapContinente.on('popupopen', removePopupHref);

                this.tileLayers.continente = this.createTileLayer();
                this.tileLayers.continente.addTo(this.mapContinente);

                this.lazyLoadMap('map-acores', 'acores', [[36.7, -28.9], [38.8, -24.7]], removePopupHref);
                this.lazyLoadMap('map-madeira', 'madeira', [[32.4, -17.4], [33.3, -16.1]], removePopupHref);

                this.renderMarkers();

                this.$watch('beaches', (newBeaches) => {
                    this.beaches = newBeaches;
                    this.renderMarkers();
                });

                window.addEventListener('beaches-updated', (event) => {
                    this.beaches = event.detail.beaches;
                    this.invalidateAllMaps();
                    this.renderMarkers();
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
                const map = L.map(elementId, {
                    zoomControl: false,
                    attributionControl: false,
                    maxZoom: 18,
                    minZoom: 6,
                    dragging: true,
                    scrollWheelZoom: true
                }).fitBounds(bounds, { padding: [10, 10] });
                map.on('popupopen', removePopupHref);
                const layer = this.createTileLayer();
                layer.addTo(map);
                this['map' + region.charAt(0).toUpperCase() + region.slice(1)] = map;
                this.tileLayers[region] = layer;
                this.renderRegionMarkers(map, this.markers[region], this.beaches.filter(b => {
                    const r = b.region || 'Continental';
                    if (region === 'acores') return r === 'Açores';
                    if (region === 'madeira') return r === 'Madeira';
                    return false;
                }));
                setTimeout(() => map.invalidateSize(), 100);
            },

            createTileLayer() {
                const isMobile = window.innerWidth < 768;
                const url = isMobile
                    ? 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png'
                    : 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}';
                const att = isMobile
                    ? '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> &copy; <a href="https://carto.com/">CARTO</a>'
                    : '&copy; <a href="https://www.esri.com/">Esri</a>';
                const layer = L.tileLayer(url, {
                    attribution: att,
                    maxZoom: 19,
                    alt: 'Mapa'
                });
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
                if (beaches.length) {
                    const coords = beaches.map(b => [b.latitude, b.longitude]);
                    map.fitBounds(coords, { padding: [10, 10], maxZoom: 12 });
                }
            },

            clearMarkers() {
                ['continente', 'acores', 'madeira'].forEach(key => {
                    const map = key === 'continente' ? this.mapContinente : key === 'acores' ? this.mapAcores : this.mapMadeira;
                    Object.values(this.markers[key]).forEach(m => map.removeLayer(m));
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
                if (!navigator.geolocation) {
                    if (!auto) alert('{{ __('common.gps_not_supported') }}');
                    return;
                }
                if (this.userCircle) {
                    if (this.mapContinente) this.mapContinente.removeLayer(this.userCircle);
                    if (this.mapAcores) this.mapAcores.removeLayer(this.userCircle);
                    if (this.mapMadeira) this.mapMadeira.removeLayer(this.userCircle);
                }
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lon = position.coords.longitude;

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
                            radius: position.coords.accuracy || 50
                        }).addTo(map).bindPopup('{{ __('common.gps_your_location') }}').openPopup();
                    },
                    (error) => {
                        if (!auto) {
                            const msgs = {
                                1: '{{ __('common.gps_denied') }}',
                                2: '{{ __('common.gps_unavailable') }}',
                                3: '{{ __('common.gps_timeout') }}',
                            };
                            alert(msgs[error.code] || '{{ __('common.gps_error') }}');
                        }
                    },
                    { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
                );
            },

            invalidateAllMaps() {
                if (this.mapContinente) this.mapContinente.invalidateSize();
                if (this.mapAcores) this.mapAcores.invalidateSize();
                if (this.mapMadeira) this.mapMadeira.invalidateSize();
            }
        }));
    </script>
    @endscript
</div>
