<div class="space-y-6" x-data="beachMapHandler(@js($beachesList))">
    @section('title', 'CheckPraia - Consulta de Bandeiras em Portugal')

    <!-- Search and Filters Panel -->
    <div class="glass-card p-4 rounded-2xl border border-white/5 space-y-4">
        <!-- Search bar -->
        <div class="w-full relative">
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search" 
                placeholder="Pesquisar praia ou município..." 
                class="w-full bg-slate-900/60 border border-white/5 px-4 py-3 pl-11 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 transition-all"
            />
            <span class="absolute left-4 top-3.5 text-slate-400">
                🔍
            </span>
        </div>

        <!-- Mobile Filter Chips -->
        <div class="w-full flex flex-col gap-3">
            <!-- Region Chips -->
            <div class="overflow-x-auto scrollbar-none flex gap-2 pb-1">
                @foreach([
                    '' => '🌐 Regiões',
                    'Continental' => '🇵🇹 Continente',
                    'Madeira' => '🌋 Madeira',
                    'Açores' => '🐋 Açores'
                ] as $value => $label)
                    <button wire:click="$set('selectedRegion', '{{ $value }}')" 
                            class="whitespace-nowrap px-3.5 py-1.5 rounded-full text-xs font-bold transition-all border {{ $selectedRegion === $value ? 'bg-blue-600 border-blue-500 text-white shadow-md shadow-blue-500/25' : 'bg-slate-900 border-white/5 text-slate-400 hover:text-slate-200' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <!-- Flag Chips -->
            <div class="overflow-x-auto scrollbar-none flex gap-2 pb-1">
                @foreach([
                    '' => '🏁 Bandeiras',
                    'green' => '🟢 Verde',
                    'yellow' => '🟡 Amarela',
                    'red' => '🔴 Vermelha',
                    'blue_or_neutral' => '🔵 Fora de Época',
                    'gray' => '⚪ Sem Info'
                ] as $value => $label)
                    <button wire:click="$set('selectedFlag', '{{ $value }}')" 
                            class="whitespace-nowrap px-3.5 py-1.5 rounded-full text-xs font-bold transition-all border {{ $selectedFlag === $value ? 'bg-blue-600 border-blue-500 text-white shadow-md shadow-blue-500/25' : 'bg-slate-900 border-white/5 text-slate-400 hover:text-slate-200' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            
            <!-- Geolocation trigger -->
            <button @click="locateUser()" class="w-full bg-slate-900/60 hover:bg-slate-800/80 text-white text-xs font-bold py-2.5 rounded-xl border border-white/5 transition-all flex items-center justify-center gap-2">
                📍 Filtrar Praias Perto de Mim (GPS)
            </button>
        </div>
    </div>

    <!-- Split Content View -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 min-h-[550px]">
        
        <!-- Left Side: Beach Cards List -->
        <div class="lg:col-span-5 flex flex-col gap-4 max-h-[600px] overflow-y-auto pr-2" :class="viewState === 'list' ? 'flex' : 'hidden lg:flex'">
            @forelse($beachesList as $beach)
                <a href="{{ $beach['url'] }}" 
                   @mouseenter="hoverBeach(@js($beach))"
                   class="glass-card p-4 rounded-xl border border-white/10 hover:border-blue-500/50 transition-all flex items-center justify-between group">
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-white group-hover:text-blue-400 transition-colors">{{ $beach['name'] }}</h3>
                            @if($beach['blue_flag'])
                                <span class="bg-blue-500/20 text-blue-300 border border-blue-500/30 text-[10px] px-1.5 py-0.5 rounded font-semibold">Bandeira Azul</span>
                            @endif
                            @if($beach['accessible'])
                                <span class="bg-teal-500/20 text-teal-300 border border-teal-500/30 text-[10px] px-1.5 py-0.5 rounded font-semibold">♿ Acessível</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-400 flex items-center gap-1">
                            <span>📍 {{ $beach['municipality'] }}, {{ $beach['region'] }}</span>
                        </p>
                    </div>

                    <!-- Flag Badge -->
                    <div class="flex flex-col items-end gap-1">
                        @if($beach['flag'] === 'green')
                            <span class="bg-emerald-500 text-slate-950 font-extrabold px-3 py-1 rounded-full text-xs shadow-md shadow-emerald-500/20 flex items-center gap-1">
                                <span class="w-2.5 h-2.5 rounded-full bg-slate-950 animate-pulse"></span> Verde
                            </span>
                        @elseif($beach['flag'] === 'yellow')
                            <span class="bg-amber-500 text-slate-950 font-extrabold px-3 py-1 rounded-full text-xs shadow-md shadow-amber-500/20 flex items-center gap-1">
                                <span class="w-2.5 h-2.5 rounded-full bg-slate-950 animate-pulse"></span> Amarela
                            </span>
                        @elseif($beach['flag'] === 'red')
                            <span class="bg-rose-500 text-white font-extrabold px-3 py-1 rounded-full text-xs shadow-md shadow-rose-500/20 flex items-center gap-1">
                                <span class="w-2.5 h-2.5 rounded-full bg-white animate-pulse"></span> Vermelha
                            </span>
                        @elseif($beach['flag'] === 'blue_or_neutral')
                            <span class="bg-blue-600 text-white font-bold px-3 py-1 rounded-full text-xs flex items-center gap-1">
                                ❄️ Fora de Época
                            </span>
                        @else
                            <span class="bg-slate-600 text-slate-300 font-bold px-3 py-1 rounded-full text-xs flex items-center gap-1">
                                🔘 Sem Dados
                            </span>
                        @endif
                        <span class="text-[9px] text-slate-500 uppercase tracking-wide">
                            {{ $beach['source'] === 'community' ? 'Comunidade' : 'Previsão' }}
                        </span>
                    </div>
                </a>
            @empty
                <div class="glass-card p-8 rounded-xl border border-white/10 text-center text-slate-400">
                    <p class="font-medium mb-1">Nenhuma praia encontrada</p>
                    <p class="text-xs text-slate-500">Tenta ajustar os teus termos de pesquisa ou filtros.</p>
                </div>
            @endforelse
        </div>

        <!-- Right Side: Interactive Leaflet Map -->
        <div class="lg:col-span-7 rounded-2xl overflow-hidden border border-white/10 relative min-h-[400px] lg:min-h-full" :class="viewState === 'map' ? 'block' : 'hidden lg:block'">
            <div id="map" class="w-full h-full absolute inset-0 z-0"></div>
        </div>

    </div>

    <!-- Floating View Toggle Button for Mobile -->
    <div class="fixed bottom-20 left-1/2 -translate-x-1/2 z-40 md:hidden">
        <button @click="viewState = (viewState === 'map' ? 'list' : 'map'); setTimeout(() => { if (viewState === 'map') map.invalidateSize(); }, 150);" 
                class="bg-blue-600 hover:bg-blue-500 text-white font-bold px-6 py-3.5 rounded-full shadow-lg shadow-blue-500/30 flex items-center gap-2 text-xs uppercase tracking-wider">
            <span x-text="viewState === 'map' ? '📋 Ver Lista' : '🗺️ Ver Mapa'"></span>
        </button>
    </div>

    <!-- Leaflet Javascript binder -->
    @script
    <script>
        Alpine.data('beachMapHandler', (initialBeaches) => ({
            map: null,
            markers: {},
            beaches: initialBeaches,
            viewState: 'map', // 'map' or 'list' on mobile
            
            init() {
                // Initialize Leaflet Map centered on Portugal
                this.map = L.map('map', {
                    zoomControl: true,
                    maxZoom: 18,
                    minZoom: 5
                }).setView([39.5, -8.5], 7);

                // OpenStreetMap tiles provider
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(this.map);

                this.renderMarkers();

                // Watch Livewire updates
                this.$watch('beaches', (newBeaches) => {
                    this.beaches = newBeaches;
                    this.renderMarkers();
                });

                // Keep Alpine data synced with Livewire component updates
                window.addEventListener('beaches-updated', (event) => {
                    this.beaches = event.detail.beaches;
                    this.renderMarkers();
                });
            },

            renderMarkers() {
                // Clear existing markers
                Object.values(this.markers).forEach(marker => this.map.removeLayer(marker));
                this.markers = {};

                // Draw new markers
                this.beaches.forEach(beach => {
                    const markerColor = this.getMarkerColor(beach.flag);
                    
                    // Custom premium glowing SVG Marker Icon
                    const customIcon = L.divIcon({
                        className: 'custom-div-icon',
                        html: `<div style="
                            width: 18px; 
                            height: 18px; 
                            background-color: ${markerColor}; 
                            border: 3px solid #070a13; 
                            border-radius: 50%;
                            box-shadow: 0 0 12px ${markerColor};
                        "></div>`,
                        iconSize: [18, 18],
                        iconAnchor: [9, 9]
                    });

                    const marker = L.marker([beach.latitude, beach.longitude], { icon: customIcon })
                        .addTo(this.map)
                        .bindPopup(`
                            <div style="color: #1e293b; font-family: sans-serif; font-size: 13px;">
                                <strong style="display:block; margin-bottom: 2px;">${beach.name}</strong>
                                <span style="font-size: 11px; color: #64748b;">📍 ${beach.municipality}</span>
                                <hr style="margin: 6px 0; border:0; border-top: 1px solid #e2e8f0;" />
                                <a href="${beach.url}" style="
                                    display: inline-block; 
                                    background-color: #2563eb; 
                                    color: white; 
                                    padding: 4px 10px; 
                                    border-radius: 6px; 
                                    text-decoration: none; 
                                    font-weight: bold; 
                                    font-size: 11px;
                                ">Ver Detalhes &rarr;</a>
                            </div>
                        `);

                    this.markers[beach.id] = marker;
                });

                // Fit bounds to markers if we have some
                if (this.beaches.length > 0) {
                    const group = new L.featureGroup(Object.values(this.markers));
                    this.map.fitBounds(group.getBounds().pad(0.1));
                }
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
                const marker = this.markers[beach.id];
                if (marker) {
                    this.map.panTo([beach.latitude, beach.longitude]);
                    marker.openPopup();
                }
            },

            locateUser() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const lat = position.coords.latitude;
                            const lon = position.coords.longitude;
                            this.map.setView([lat, lon], 12);
                            
                            // User location circle
                            L.circle([lat, lon], {
                                color: '#3b82f6',
                                fillColor: '#93c5fd',
                                fillOpacity: 0.3,
                                radius: 1000 // 1 km circle indicator
                            }).addTo(this.map).bindPopup('A tua localização actual').openPopup();
                        },
                        (error) => {
                            alert('Não foi possível obter a tua localização. Verifica as permissões.');
                        }
                    );
                } else {
                    alert('Geolocalização não suportada pelo teu navegador.');
                }
            }
        }));
    </script>
    @endscript
</div>
