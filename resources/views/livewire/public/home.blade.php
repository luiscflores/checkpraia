<div class="space-y-3 sm:space-y-6" x-data="beachMapHandler(@js($beachesList))">
    @section('title', 'CheckPraia')

    <h1 class="sr-only">CheckPraia - Mapa das Praias</h1>

    @if(session()->has('favorite_success'))
        <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 text-xs rounded-xl font-medium">
            {{ session('favorite_success') }}
        </div>
    @endif

    @if(session()->has('favorite_error'))
        <div class="p-3 bg-rose-500/10 border border-rose-500/20 text-rose-200 text-xs rounded-xl font-medium">
            {{ session('favorite_error') }}
        </div>
    @endif

    <!-- Search and Filters Panel -->
    <div class="glass-card p-3 sm:p-4 rounded-2xl border border-theme-subtle space-y-3">
        <!-- Search bar + GPS -->
        <div class="flex items-stretch gap-2">
            <div class="w-full relative flex-1">
                <label for="beach-search" class="sr-only">Pesquisar praia ou município</label>
                <input 
                    id="beach-search"
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Pesquisar praia ou município..." 
                    class="w-full bg-theme-input border border-theme-subtle px-4 py-3.5 pl-10 rounded-xl text-base sm:text-sm text-theme placeholder:text-theme-muted focus:outline-none focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/20 transition-all"
                />
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-theme-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <!-- GPS Trigger -->
            <button @click="locateUser()" class="shrink-0 bg-theme-card active:scale-95 text-theme text-sm font-semibold px-3 sm:px-5 py-3.5 rounded-xl border border-theme-subtle hover:border-blue-500/30 transition-all flex items-center justify-center gap-1.5 touch-target group" title="Praias perto de mim">
                <svg class="w-5 h-5 text-blue-400 group-hover:text-blue-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span class="hidden sm:inline">Praias perto de mim</span>
            </button>
        </div>

        <!-- Flag Filters -->
        <div class="flex flex-wrap gap-1.5">
            <button wire:click="$set('selectedFlag', '')" 
                    class="basis-[30%] sm:basis-auto grow sm:grow-0 whitespace-nowrap px-3 py-2.5 rounded-full text-sm font-semibold transition-all border touch-target min-h-[42px] flex items-center justify-center gap-1.5 {{ $selectedFlag === '' ? 'bg-blue-600/20 border-blue-500/40 text-blue-300 shadow-sm' : 'bg-theme-card border-theme-subtle text-theme-secondary hover:text-theme hover:border-theme-medium' }}">
                <span>🏁</span>
                <span class="text-xs sm:text-sm">Todas</span>
            </button>
            @foreach($flagFilters as $value => $label)
                @if($value !== '') 
                    <button wire:click="$set('selectedFlag', '{{ $value }}')" 
                            class="basis-[30%] sm:basis-auto grow sm:grow-0 whitespace-nowrap px-3 py-2.5 rounded-full text-sm font-semibold transition-all border touch-target min-h-[42px] flex items-center justify-center gap-1.5 {{ $selectedFlag === $value ? 'bg-blue-600/20 border-blue-500/40 text-blue-300 shadow-sm' : 'bg-theme-card border-theme-subtle text-theme-secondary hover:text-theme hover:border-theme-medium' }}">
                        <span>{{ $flagIcons[$value] ?? '' }}</span>
                        <span class="text-xs sm:text-sm">{{ $label }}</span>
                    </button>
                @endif
            @endforeach
        </div>
    </div>

    <!-- Split Content View -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 sm:gap-6 min-h-[450px] sm:min-h-[550px]">
        
        <!-- Left Side: Beach Cards List -->
        <div class="lg:col-span-5 flex flex-col gap-3 max-h-[calc(100vh-220px)] lg:max-h-[700px] overflow-y-auto pr-0.5 sm:pr-2" :class="viewState === 'list' ? 'flex' : 'hidden lg:flex'">
            @forelse($beachesList as $beach)
                <a href="{{ $beach['url'] }}" 
                   @mouseenter="hoverBeach(@js($beach))"
                   class="glass-card shrink-0 p-3.5 rounded-2xl border transition-all duration-300 flex flex-col gap-3 group active:scale-[0.99] relative overflow-hidden {{ $beach['is_favorited'] ? 'border-amber-500/40 bg-amber-500/[0.04] shadow-[inset_0_0_12px_rgba(245,158,11,0.05)]' : 'border-theme-medium hover:border-blue-500/40' }}">
                    
                    @if($beach['is_favorited'])
                        <div class="absolute top-0 left-0 w-1 h-full bg-amber-500"></div>
                    @endif

                    <!-- Top Row: Title, Location and Star -->
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0 flex-1">
                            <h2 class="font-extrabold text-base sm:text-lg text-theme group-hover:text-blue-400 transition-colors truncate tracking-tight">{{ $beach['name'] }}</h2>
                            <div class="flex items-center gap-1 mt-0.5 text-xs text-theme-secondary">
                                <svg class="w-3.5 h-3.5 text-theme-muted shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span class="truncate">{{ $beach['municipality'] }}, {{ $beach['region'] }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 shrink-0">
                            <!-- Favorite button -->
                            <button type="button"
                                    @click.prevent.stop="$wire.toggleFavorite({{ $beach['id'] }})"
                                    class="p-1 rounded-lg text-lg transition-all hover:scale-110 active:scale-95 hover:bg-white/5 {{ $beach['is_favorited'] ? 'opacity-100 drop-shadow-[0_0_8px_rgba(245,158,11,0.6)] text-amber-400' : 'opacity-40 hover:opacity-85 text-theme-secondary' }}"
                                    title="{{ $beach['is_favorited'] ? 'Remover dos favoritos' : 'Adicionar aos favoritos' }}">
                                ⭐
                            </button>

                            <!-- Flag Status Badge -->
                            <div>
                                @if($beach['flag'] === 'green')
                                    <span class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 font-bold px-2.5 py-1 rounded-full text-xs leading-none flex items-center gap-1 shadow-sm shadow-emerald-500/5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span>Verde</span>
                                    </span>
                                @elseif($beach['flag'] === 'yellow')
                                    <span class="bg-amber-500/10 border border-amber-500/30 text-amber-400 font-bold px-2.5 py-1 rounded-full text-xs leading-none flex items-center gap-1 shadow-sm shadow-amber-500/5">
                                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                        <span>Amarela</span>
                                    </span>
                                @elseif($beach['flag'] === 'red')
                                    <span class="bg-rose-500/10 border border-rose-500/30 text-rose-400 font-bold px-2.5 py-1 rounded-full text-xs leading-none flex items-center gap-1 shadow-sm shadow-rose-500/5">
                                        <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                                        <span>Vermelha</span>
                                    </span>
                                @elseif($beach['flag'] === 'blue_or_neutral')
                                    <span class="bg-blue-500/10 border border-blue-500/30 text-blue-400 font-bold px-2.5 py-1 rounded-full text-xs leading-none flex items-center gap-1 shadow-sm shadow-blue-500/5">
                                        <span class="text-xs">❄️</span>
                                        <span>Fora de Época</span>
                                    </span>
                                @else
                                    <span class="bg-slate-500/10 border border-slate-500/30 text-slate-400 font-bold px-2.5 py-1 rounded-full text-xs leading-none flex items-center gap-1">
                                        <span class="text-xs">—</span>
                                        <span>Sem Info</span>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Tags Row (Blue Flag, Accessible, Jellyfish, Verification Source) -->
                    <div class="flex flex-wrap gap-1.5">
                        @if($beach['blue_flag'])
                            <span class="bg-blue-600/15 border border-blue-500/20 text-blue-400 text-[10px] px-2 py-0.5 rounded-md font-bold uppercase tracking-wider">
                                Bandeira Azul
                            </span>
                        @endif
                        @if($beach['accessible'])
                            <span class="bg-teal-600/15 border border-teal-500/20 text-teal-400 text-[10px] px-2 py-0.5 rounded-md font-bold uppercase tracking-wider">
                                Acessível
                            </span>
                        @endif

                        <!-- Verification Source Indicator -->
                        @if($beach['source'] === 'report' || $beach['source'] === 'consensus')
                            <span class="bg-emerald-600/15 border border-emerald-500/20 text-emerald-400 text-[10px] px-2 py-0.5 rounded-md font-medium flex items-center gap-1 ml-auto">
                                <svg class="w-3 h-3 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a.75.75 0 00-.708.522L3.547 11.22a.75.75 0 00.328.878l5.25 3a.75.75 0 00.75 0l5.25-3a.75.75 0 00.328-.878l-2.012-7.243a.75.75 0 00-.708-.522H6.267zm1.666 4.47a.75.75 0 011.06 0l1.5 1.5a.75.75 0 010 1.06l-3 3a.75.75 0 01-1.06-1.06l2.47-2.47-1.97-1.97a.75.75 0 010-1.06zm4.814 1.28a.75.75 0 10-1.06-1.06 4 4 0 100 5.66.75.75 0 101.06-1.06 2.5 2.5 0 110-3.54z" clip-rule="evenodd"/></svg>
                                Confirmado
                            </span>
                        @else
                            <span class="bg-blue-600/10 border border-blue-500/10 text-blue-400/80 text-[10px] px-2 py-0.5 rounded-md font-normal flex items-center gap-1 ml-auto">
                                Previsão
                            </span>
                        @endif
                    </div>

                    <!-- Weather & Wave Info Grid -->
                    <div class="grid grid-cols-4 gap-1.5 pt-2.5 border-t border-white/[0.04] [data-theme=light]:border-black/[0.04]">

                        <!-- Air Temp -->
                        <div class="flex flex-col items-center justify-center p-1.5 rounded-xl bg-theme-input border border-theme-subtle text-center" title="Temperatura do Ar">
                            <span class="text-[10px] text-theme-muted font-medium mb-0.5 flex items-center gap-0.5">
                                <svg class="w-3 h-3 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z"/>
                                </svg>
                                <span>Ar</span>
                            </span>
                            <span class="text-xs sm:text-sm font-bold text-theme">
                                {{ $beach['air_temp'] !== null ? $beach['air_temp'] . '°C' : '—' }}
                            </span>
                        </div>

                        <!-- Water Temp -->
                        <div class="flex flex-col items-center justify-center p-1.5 rounded-xl bg-theme-input border border-theme-subtle text-center" title="Temperatura da Água">
                            <span class="text-[10px] text-theme-muted font-medium mb-0.5 flex items-center gap-0.5">
                                <svg class="w-3 h-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>
                                </svg>
                                <span>Água</span>
                            </span>
                            <span class="text-xs sm:text-sm font-bold text-theme">
                                {{ $beach['water_temp'] !== null ? $beach['water_temp'] . '°C' : '—' }}
                            </span>
                        </div>

                        <!-- Wave Height -->
                        <div class="flex flex-col items-center justify-center p-1.5 rounded-xl bg-theme-input border border-theme-subtle text-center" title="Altura das Ondas">
                            <span class="text-[10px] text-theme-muted font-medium mb-0.5 flex items-center gap-0.5">
                                <svg class="w-3 h-3 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2 12c4-4 8 4 12 0s8-4 12 0M2 16c4-4 8 4 12 0s8-4 12 0"/>
                                </svg>
                                <span>Ondas</span>
                            </span>
                            <span class="text-xs sm:text-sm font-bold text-theme">
                                @if($beach['wave_height_max'] !== null)
                                    {{ $beach['wave_height_max'] }}m
                                @else
                                    —
                                @endif
                            </span>
                        </div>

                        <!-- Wind -->
                        <div class="flex flex-col items-center justify-center p-1.5 rounded-xl bg-theme-input border border-theme-subtle text-center" title="Velocidade e Direção do Vento">
                            <span class="text-[10px] text-theme-muted font-medium mb-0.5 flex items-center gap-0.5">
                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.59 4.59A2 2 0 1 1 11 8H2m10.59 11.41A2 2 0 1 0 14 16H2m15.73-8.27A2.5 2.5 0 1 1 19.5 12H2"/>
                                </svg>
                                <span>Vento</span>
                            </span>
                            <span class="text-xs sm:text-sm font-bold text-theme flex items-center justify-center gap-1">
                                <span>{{ $beach['wind_speed'] !== null ? (int)round($beach['wind_speed'] * 1.852) . ' km/h' : '—' }}</span>
                                @if($beach['wind_direction'] !== null)
                                    <span class="text-[10px] text-slate-400">{{ $beach['wind_direction'] }}</span>
                                @endif
                            </span>
                        </div>

                    </div>

                </a>
            @empty
                <div class="glass-card p-6 sm:p-8 rounded-xl border border-theme-medium text-center text-theme-secondary">
                    <p class="font-medium text-base sm:text-lg mb-1">Nenhuma praia encontrada</p>
                    <p class="text-sm text-theme-muted">Tenta ajustar a pesquisa ou filtros.</p>
                </div>
            @endforelse
        </div>

        <!-- Right Side: Maps -->
        <div wire:ignore class="lg:col-span-7 flex flex-col gap-2 sm:gap-3 min-h-[300px] sm:min-h-[400px] lg:min-h-full" :class="viewState === 'map' ? 'flex' : 'hidden lg:flex'">

            <!-- Main Map - Continental Portugal -->
            <div class="flex-1 rounded-xl sm:rounded-2xl overflow-hidden border border-theme-medium relative min-h-[240px] sm:min-h-[280px]">
                <div id="map-continente" class="w-full h-full absolute inset-0 z-0" role="application" aria-label="Mapa de praias de Portugal Continental"></div>
            </div>

            <!-- Island Maps Row -->
            <div class="grid grid-cols-2 gap-2 sm:gap-3 h-28 sm:h-36 shrink-0">
                <!-- Azores -->
                <div class="rounded-lg sm:rounded-xl overflow-hidden border border-theme-medium relative">
                    <div id="map-acores" class="w-full h-full absolute inset-0 z-0" role="application" aria-label="Mapa de praias dos Açores"></div>
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent pt-5 pb-1.5 px-2.5 z-10" aria-hidden="true">
                        <span class="text-white/90 text-xs sm:text-sm font-bold tracking-widest uppercase">Açores</span>
                    </div>
                </div>
                <!-- Madeira -->
                <div class="rounded-lg sm:rounded-xl overflow-hidden border border-theme-medium relative">
                    <div id="map-madeira" class="w-full h-full absolute inset-0 z-0" role="application" aria-label="Mapa de praias da Madeira"></div>
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent pt-5 pb-1.5 px-2.5 z-10" aria-hidden="true">
                        <span class="text-white/90 text-xs sm:text-sm font-bold tracking-widest uppercase">Madeira</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Floating View Toggle Button -->
    <div class="fixed bottom-20 sm:bottom-24 left-1/2 -translate-x-1/2 z-40 md:hidden pb-safe">
        <button @click="viewState = (viewState === 'map' ? 'list' : 'map'); setTimeout(() => { if (viewState === 'map') invalidateAllMaps(); }, 150);" 
                class="bg-blue-600 hover:bg-blue-500 active:scale-90 text-white font-bold px-5 py-3.5 rounded-full shadow-lg shadow-blue-500/25 flex items-center gap-2 text-sm uppercase tracking-wider touch-target transition-all backdrop-blur-sm bg-blue-600/90">
            <svg x-show="viewState === 'map'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            <svg x-show="viewState === 'list'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
            </svg>
            <span x-text="viewState === 'map' ? 'Lista' : 'Mapa'"></span>
        </button>
    </div>

    <!-- Leaflet Javascript binder -->
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

                this.mapAcores = L.map('map-acores', {
                    zoomControl: false,
                    attributionControl: false,
                    maxZoom: 18,
                    minZoom: 6,
                    dragging: true,
                    scrollWheelZoom: true
                }).fitBounds([[36.7, -28.9], [38.8, -24.7]], { padding: [10, 10] });
                this.mapAcores.on('popupopen', removePopupHref);

                this.tileLayers.acores = this.createTileLayer();
                this.tileLayers.acores.addTo(this.mapAcores);

                this.mapMadeira = L.map('map-madeira', {
                    zoomControl: false,
                    attributionControl: false,
                    maxZoom: 18,
                    minZoom: 7,
                    dragging: true,
                    scrollWheelZoom: true
                }).fitBounds([[32.4, -17.4], [33.3, -16.1]], { padding: [10, 10] });
                this.mapMadeira.on('popupopen', removePopupHref);

                this.tileLayers.madeira = this.createTileLayer();
                this.tileLayers.madeira.addTo(this.mapMadeira);

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

            createTileLayer() {
                const layer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    attribution: '&copy; <a href="https://www.esri.com/">Esri</a>',
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
                                <a href="${beach.url}" class="beach-popup-btn">Ver Detalhes →</a>
                            </div>
                        `, { className: 'beach-popup', closeButton: false, maxWidth: 260, minWidth: 160 });
                    
                    const markerEl = marker.getElement();
                    if (markerEl) {
                        markerEl.setAttribute('aria-label', `Marcador de ${beach.name}`);
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
                if (r === 'Açores') { map = this.mapAcores; store = this.markers.acores; }
                else if (r === 'Madeira') { map = this.mapMadeira; store = this.markers.madeira; }
                else { map = this.mapContinente; store = this.markers.continente; }
                const marker = store[beach.id];
                if (marker) {
                    map.panTo([beach.latitude, beach.longitude]);
                    marker.openPopup();
                }
            },

            locateUser(auto = false) {
                if (!navigator.geolocation) {
                    if (!auto) alert('Geolocalização não suportada pelo teu navegador.');
                    return;
                }
                if (this.userCircle) {
                    this.mapContinente.removeLayer(this.userCircle);
                    this.mapAcores.removeLayer(this.userCircle);
                    this.mapMadeira.removeLayer(this.userCircle);
                }
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lon = position.coords.longitude;

                        // Determine which region the user is in
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
                        }).addTo(map).bindPopup('A tua localização actual').openPopup();
                    },
                    (error) => {
                        if (!auto) {
                            const msgs = {
                                1: 'Permissão de localização negada. Ativa nos definições do dispositivo.',
                                2: 'Sinal GPS indisponível. Tenta num local com céu aberto.',
                                3: 'Pedido de localização expirou. Tenta novamente.',
                            };
                            alert(msgs[error.code] || 'Erro de geolocalização.');
                        }
                    },
                    { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
                );
            },

            invalidateAllMaps() {
                this.mapContinente.invalidateSize();
                this.mapAcores.invalidateSize();
                this.mapMadeira.invalidateSize();
            }
        }));
    </script>
    @endscript
</div>