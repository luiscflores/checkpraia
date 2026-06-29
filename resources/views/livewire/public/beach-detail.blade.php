<div class="space-y-8" x-data="{
    locating: false,
    triggerReport(flagColor) {
        this.locating = true;
        if (!navigator.geolocation) {
            alert('O teu navegador não suporta geolocalização.');
            this.locating = false;
            return;
        }
        navigator.geolocation.getCurrentPosition(
            (position) => {
                @this.call('submitReport', flagColor, position.coords.latitude, position.coords.longitude, position.coords.accuracy);
                this.locating = false;
            },
            (error) => {
                alert('Erro de GPS: A permissão de localização é obrigatória para confirmar a bandeira.');
                this.locating = false;
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    }
}">
    @section('title', $beach->name . ' - Bandeira e Condições do Mar')

    <!-- Beach Header Banner -->
    <div class="glass-card p-6 md:p-8 rounded-3xl border border-white/10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-3 flex-wrap">
                <span class="text-xs uppercase tracking-widest text-blue-400 bg-blue-500/10 px-3 py-1 rounded-full border border-blue-500/20 font-bold">
                    {{ $beach->region }}
                </span>
                @if($beach->blue_flag)
                    <span class="text-[10px] uppercase font-bold text-white bg-blue-600 px-2 py-0.5 rounded-md border border-white/10">🔷 Bandeira Azul</span>
                @endif
                @if($beach->accessible)
                    <span class="text-[10px] uppercase font-bold text-white bg-teal-600 px-2 py-0.5 rounded-md border border-white/10">♿ Praia Acessível</span>
                @endif
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight mt-2">{{ $beach->name }}</h1>
            <p class="text-slate-400 text-sm mt-1">📍 {{ $beach->municipality }}, {{ $beach->district ?: $beach->region }}</p>
        </div>

        <div class="flex gap-3">
            <a href="https://www.google.com/maps/dir/?api=1&destination={{ $beach->latitude }},{{ $beach->longitude }}" 
               target="_blank" 
               class="bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white px-4 py-2.5 rounded-xl border border-slate-700 text-sm font-semibold transition-all flex items-center gap-2">
                🗺️ Direções de GPS
            </a>
        </div>
    </div>

    <!-- Alerts Notification Area -->
    @foreach($alerts as $alert)
        <div class="p-4 rounded-2xl border {{ $alert->type === 'warning' ? 'border-amber-500/30 bg-amber-950/20 text-amber-200' : 'border-rose-500/30 bg-rose-950/20 text-rose-200' }} text-sm leading-relaxed shadow-sm">
            <strong class="uppercase font-bold block mb-1">⚠️ Aviso Oficial:</strong>
            {{ $alert->description }} (Início: {{ $alert->started_at->format('d/m/Y H:i') }})
        </div>
    @endforeach

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Left Column: Flag Card and GPS Reporter (Span 5) -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Active Flag Card -->
            <div class="glass-card p-6 rounded-3xl text-center flex flex-col items-center justify-center relative overflow-hidden">
                <div class="absolute w-48 h-48 rounded-full blur-3xl opacity-20 -top-12 -left-12 bg-blue-500"></div>

                <span class="text-xs uppercase tracking-widest text-slate-400 font-bold mb-3">Estado da Bandeira</span>

                @php
                    $flag = $beach->currentStatus ? $beach->currentStatus->flag : 'gray';
                    $source = $beach->currentStatus ? $beach->currentStatus->source : 'prediction';
                    $confidence = $beach->currentStatus ? $beach->currentStatus->confidence : 100;
                    $flagName = match($flag) {
                        'green' => 'Verde',
                        'yellow' => 'Amarela',
                        'red' => 'Vermelha',
                        'blue_or_neutral' => 'Fora de Época',
                        default => 'Indisponível'
                    };
                    $glowColor = match($flag) {
                        'green' => 'rgba(16, 185, 129, 0.4)',
                        'yellow' => 'rgba(245, 158, 11, 0.4)',
                        'red' => 'rgba(239, 68, 68, 0.4)',
                        'blue_or_neutral' => 'rgba(59, 130, 246, 0.4)',
                        default => 'rgba(107, 114, 128, 0.4)'
                    };
                    $flagBg = match($flag) {
                        'green' => 'bg-emerald-500 text-slate-950',
                        'yellow' => 'bg-amber-500 text-slate-950',
                        'red' => 'bg-rose-500 text-white',
                        'blue_or_neutral' => 'bg-blue-600 text-white',
                        default => 'bg-slate-600 text-slate-300'
                    };
                    $markerColorHex = match($flag) {
                        'green' => '#10b981',
                        'yellow' => '#f59e0b',
                        'red' => '#ef4444',
                        'blue_or_neutral' => '#3b82f6',
                        default => '#6b7280'
                    };
                @endphp

                <div class="w-32 h-32 rounded-full flex items-center justify-center shadow-2xl transition-all duration-300 {{ $flagBg }}" 
                     style="box-shadow: 0 12px 48px {{ $glowColor }}">
                    <span class="text-xl font-black uppercase tracking-wider">{{ $flagName }}</span>
                </div>

                <div class="mt-6 space-y-1">
                    <p class="text-sm font-semibold text-slate-200">
                        Origem: 
                        <span class="text-blue-400">
                            {{ $source === 'community' ? 'Confirmação Comunitária' : ($source === 'alert' ? 'Aviso Oficial' : 'Previsão Automática') }}
                        </span>
                    </p>
                    <p class="text-xs text-slate-400">Grau de Confiança: <span class="font-bold text-white">{{ $confidence }}%</span></p>
                    <p class="text-[10px] text-slate-500">Última atualização: {{ $beach->updated_at->format('H:i') }}</p>
                </div>

                @if($source === 'prediction' && isset($prediction) && $prediction->selected_flag !== 'gray')
                    <div class="mt-4 w-full max-w-xs space-y-2">
                        <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider block">Distribuição de Probabilidades</span>
                        <div class="h-4 w-full rounded-full bg-slate-800/80 flex overflow-hidden shadow-inner border border-white/5 p-[2px]">
                            @if($prediction->green_probability > 0)
                                <div class="bg-emerald-500 rounded-l-full transition-all duration-300 flex items-center justify-center text-[9px] font-black text-slate-950" 
                                     style="width: {{ $prediction->green_probability }}%" 
                                     title="Verde: {{ $prediction->green_probability }}%">
                                    @if($prediction->green_probability >= 20) {{ $prediction->green_probability }}% @endif
                                </div>
                            @endif
                            @if($prediction->yellow_probability > 0)
                                <div class="bg-amber-500 transition-all duration-300 flex items-center justify-center text-[9px] font-black text-slate-950" 
                                     style="width: {{ $prediction->yellow_probability }}%" 
                                     title="Amarela: {{ $prediction->yellow_probability }}%">
                                    @if($prediction->yellow_probability >= 20) {{ $prediction->yellow_probability }}% @endif
                                </div>
                            @endif
                            @if($prediction->red_probability > 0)
                                <div class="bg-rose-500 rounded-r-full transition-all duration-300 flex items-center justify-center text-[9px] font-black text-white" 
                                     style="width: {{ $prediction->red_probability }}%" 
                                     title="Vermelha: {{ $prediction->red_probability }}%">
                                    @if($prediction->red_probability >= 20) {{ $prediction->red_probability }}% @endif
                                </div>
                            @endif
                        </div>
                        
                        @php
                            $g = $prediction->green_probability;
                            $y = $prediction->yellow_probability;
                            $r = $prediction->red_probability;
                            $helperText = 'Previsão estável com tendência clara.';
                            if ($g >= 30 && $y >= 30) {
                                $helperText = 'Tendência mista entre Verde e Amarela (mar de transição).';
                            } elseif ($y >= 30 && $r >= 30) {
                                $helperText = 'Tendência instável entre Amarela e Vermelha (mar a piorar).';
                            } elseif ($g >= 30 && $r >= 30) {
                                $helperText = 'Condições meteorológicas e marítimas voláteis.';
                            }
                        @endphp
                        <span class="text-[10px] text-slate-400 block leading-tight font-medium">💡 {{ $helperText }}</span>
                    </div>
                @endif

                @if($beach->currentStatus && $beach->currentStatus->reason)
                    <div class="mt-4 p-3 rounded-xl border border-white/5 bg-slate-900/60 max-w-xs text-center">
                        <p class="text-xs text-slate-300 font-medium leading-relaxed">
                            🔍 {{ $beach->currentStatus->reason }}
                        </p>
                    </div>
                @endif
            </div>

            <!-- GPS Confirmation Reporter -->
            <div class="glass-card p-6 rounded-3xl space-y-4">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    📢 Confirmar Bandeira no Local
                </h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Ajuda a comunidade! Se estás fisicamente nesta praia, reporta a cor da bandeira hasteada. A tua localização será validada.
                </p>

                @auth
                    @if (session()->has('report_success'))
                        <div class="p-3 bg-emerald-500/20 border border-emerald-500/30 text-emerald-200 text-xs rounded-xl font-medium">
                            ✔️ {{ session('report_success') }}
                        </div>
                    @endif

                    @error('report')
                        <div class="p-3 bg-rose-500/20 border border-rose-500/30 text-rose-200 text-xs rounded-xl font-medium">
                            ❌ {{ $message }}
                        </div>
                    @enderror

                    <div x-show="locating" class="p-3 bg-blue-500/10 border border-blue-500/20 text-blue-300 text-xs rounded-xl flex items-center gap-2">
                        <span class="animate-spin">🌀</span> Obtendo coordenadas GPS precisas...
                    </div>

                    <div class="grid grid-cols-3 gap-3" x-show="!locating">
                        <button @click="triggerReport('green')" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold py-3 rounded-xl text-xs transition-all shadow shadow-emerald-500/20">
                            🟢 Verde
                        </button>
                        <button @click="triggerReport('yellow')" class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold py-3 rounded-xl text-xs transition-all shadow shadow-amber-500/20">
                            🟡 Amarela
                        </button>
                        <button @click="triggerReport('red')" class="bg-rose-500 hover:bg-rose-400 text-white font-bold py-3 rounded-xl text-xs transition-all shadow shadow-rose-500/20">
                            🔴 Vermelha
                        </button>
                    </div>
                @else
                    <div class="p-4 bg-slate-800/80 rounded-2xl border border-slate-700 text-center text-xs">
                        Para confirmar a bandeira, deves 
                        <a href="{{ route('profile') }}" class="text-blue-400 hover:underline font-bold">iniciar sessão</a>.
                    </div>
                @endauth
            </div>
        </div>

        <!-- Right Column: Details & Forecast (Span 7) -->
        <div class="lg:col-span-7 space-y-6">
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div class="glass-card p-4 rounded-2xl text-center space-y-1">
                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Ondulação Máxima</span>
                    <span class="text-xl font-bold text-white block">{{ $ocean && $ocean->wave_height_max !== null ? $ocean->wave_height_max . 'm' : 'Sem Dados' }}</span>
                    @if($ocean && $ocean->wave_direction)
                        <span class="text-[10px] text-slate-500 block">Dir: {{ $ocean->wave_direction }}</span>
                    @endif
                </div>

                <div class="glass-card p-4 rounded-2xl text-center space-y-1">
                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Temp. da Água</span>
                    <span class="text-xl font-bold text-white block">{{ $ocean && $ocean->water_temp !== null ? $ocean->water_temp . '°C' : 'Sem Dados' }}</span>
                    <span class="text-[10px] text-slate-500 block">SST Média</span>
                </div>

                <div class="glass-card p-4 rounded-2xl text-center space-y-1">
                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Intensidade Vento</span>
                    <span class="text-xl font-bold text-white block">{{ $weather && $weather->wind_speed !== null ? (int)$weather->wind_speed . ' kt' : 'Sem Dados' }}</span>
                    @if($weather && $weather->wind_direction)
                        <span class="text-[10px] text-slate-500 block">Dir: {{ $weather->wind_direction }}</span>
                    @endif
                </div>

                <div class="glass-card p-4 rounded-2xl text-center space-y-1">
                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Qualidade Água</span>
                    @php
                        $qualityVal = $quality && $quality->quality_class && $quality->quality_class !== 'Desconhecido' ? $quality->quality_class : 'Sem Dados';
                        $qualityColor = match($qualityVal) {
                            'Excellent' => 'text-emerald-400',
                            'Good' => 'text-teal-400',
                            'Sufficient' => 'text-amber-400',
                            'Poor' => 'text-rose-500',
                            default => 'text-slate-400'
                        };
                        $qualityText = match($qualityVal) {
                            'Excellent' => 'Excelente',
                            'Good' => 'Boa',
                            'Sufficient' => 'Suficiente',
                            'Poor' => 'Imprópria',
                            default => 'Sem Dados'
                        };
                    @endphp
                    <span class="text-xl font-bold block {{ $qualityColor }}">{{ $qualityText }}</span>
                    <span class="text-[10px] text-slate-500 block">Amostra recente</span>
                </div>

                <div class="glass-card p-4 rounded-2xl text-center space-y-1">
                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Índice UV</span>
                    @php
                        $uv = $weather && $weather->uv_index !== null ? (float) $weather->uv_index : null;
                        $uvClass = 'text-slate-400';
                        $uvLabel = 'Sem Dados';
                        if ($uv !== null) {
                            $uvClass = match(true) {
                                $uv >= 8.0 => 'text-rose-500 font-extrabold',
                                $uv >= 6.0 => 'text-orange-400 font-bold',
                                $uv >= 3.0 => 'text-amber-400 font-semibold',
                                default => 'text-emerald-400'
                            };
                            $uvLabel = match(true) {
                                $uv >= 8.0 => 'Muito Alto ⚠️',
                                $uv >= 6.0 => 'Alto',
                                $uv >= 3.0 => 'Moderado',
                                default => 'Baixo'
                            };
                        }
                    @endphp
                    <span class="text-xl font-bold block {{ $uvClass }}">{{ $uv !== null ? $uv : 'Sem Dados' }}</span>
                    <span class="text-[10px] text-slate-500 block">{{ $uvLabel }}</span>
                </div>

                <div class="glass-card p-4 rounded-2xl text-center space-y-1">
                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Alforrecas</span>
                    @php
                        $jelly = $weather && $weather->jellyfish_risk ? $weather->jellyfish_risk : null;
                        $jellyClass = 'text-slate-400';
                        $jellyLabel = 'Sem Dados';
                        if ($jelly !== null) {
                            $jellyClass = match($jelly) {
                                'Alto' => 'text-rose-500 font-extrabold',
                                'Moderado' => 'text-amber-400 font-bold',
                                default => 'text-emerald-400'
                            };
                            $jellyLabel = $jelly;
                        }
                    @endphp
                    <span class="text-xl font-bold block {{ $jellyClass }}">{{ $jellyLabel }}</span>
                    <span class="text-[10px] text-slate-500 block">GelAvista</span>
                </div>
            </div>

            <!-- Description & Services -->
            <div class="glass-card p-6 rounded-3xl space-y-6">
                <div class="space-y-2">
                    <h3 class="text-lg font-bold text-white">Sobre a Praia</h3>
                    <p class="text-sm text-slate-300 leading-relaxed">
                        {{ $beach->description ?: 'Esta praia oficial vigiada apresenta uma excelente época balnear e águas com classificações periódicas ótimas.' }}
                    </p>
                </div>

                <div class="space-y-3">
                    <h3 class="text-sm uppercase tracking-wide text-slate-400 font-bold">Serviços Disponíveis</h3>
                    @if($beach->services)
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs text-slate-200">
                            @foreach([
                                'parking' => '🚗 Estacionamento',
                                'bathrooms' => '🚻 Casas de Banho',
                                'showers' => '🚿 Chuveiros',
                                'accessible' => '♿ Acessibilidade',
                                'amphibious_chair' => '🦽 Cadeira Anfíbia',
                                'first_aid' => '➕ Primeiros Socorros',
                                'lifeguard_post' => '🛟 Nadador-Salvador',
                                'bar' => '🍹 Bar / Café',
                                'restaurant' => '🍽️ Restaurante',
                                'surf_school' => '🏄 Escola de Surf',
                                'equipment_rental' => '🛶 Aluguer de Equipamento',
                            ] as $field => $label)
                                @if($beach->services->$field)
                                    <div class="bg-white/5 border border-white/5 px-2.5 py-2 rounded-lg flex items-center gap-1.5 font-medium">
                                        {{ $label }}
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-slate-500">Sem serviços cadastrados no catálogo oficial.</p>
                    @endif
                </div>
            </div>

            <!-- TripAdvisor & TheFork Dining integrations -->
            <div class="space-y-4">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    🍴 Onde Comer por Perto
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($beach->restaurants as $restaurant)
                        <div class="glass-card p-4 rounded-2xl border border-white/10 flex flex-col justify-between gap-3 relative group">
                            <span class="absolute top-3 right-3 text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full {{ $restaurant->source === 'tripadvisor' ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-300 border border-amber-500/20' }}">
                                {{ $restaurant->source }}
                            </span>

                            <div class="space-y-1">
                                <h4 class="font-bold text-white group-hover:text-blue-400 transition-colors text-sm pr-16">{{ $restaurant->name }}</h4>
                                <p class="text-xs text-slate-400">{{ $restaurant->cuisine_type }}</p>
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="text-yellow-400">★ {{ $restaurant->rating }}</span>
                                    <span class="text-slate-500">({{ $restaurant->reviews_count }} avaliações)</span>
                                </div>
                                @if($restaurant->average_price)
                                    <p class="text-xs text-slate-300">Preço Médio: <span class="font-bold text-white">{{ $restaurant->average_price }} €</span></p>
                                @endif
                                <p class="text-[10px] text-slate-500">Distância da praia: {{ round($restaurant->pivot->distance, 2) }} km</p>
                            </div>

                            <div class="flex gap-2 pt-2 border-t border-white/5">
                                @if($restaurant->booking_url)
                                    <a href="{{ $restaurant->booking_url }}" target="_blank" class="flex-1 text-center bg-blue-600 hover:bg-blue-500 text-white text-[11px] font-bold py-1.5 rounded-lg transition-colors">
                                        Reservar Mesa
                                    </a>
                                @endif
                                <a href="{{ $restaurant->external_url }}" target="_blank" class="flex-1 text-center bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-[11px] font-bold py-1.5 rounded-lg transition-colors border border-slate-700">
                                    Ver Ficha
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 glass-card p-6 rounded-xl border border-white/10 text-center text-slate-500 text-xs">
                            Sem recomendações de restaurantes disponíveis na cache do TripAdvisor ou TheFork para esta praia.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet single location map section -->
    <div class="glass-card p-4 rounded-3xl border border-white/10">
        <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-3">📍 Localização Geográfica</h3>
        <div id="beach-map" class="w-full h-80 rounded-2xl border border-white/5 overflow-hidden z-0"
             x-init="
                const map = L.map('beach-map').setView([{{ $beach->latitude }}, {{ $beach->longitude }}], 14);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);
                
                const markerColor = '{{ $markerColorHex }}';
                const icon = L.divIcon({
                    className: 'detail-div-icon',
                    html: `<div style='width: 20px; height: 20px; background-color: ${markerColor}; border: 3.5px solid #070a13; border-radius: 50%; box-shadow: 0 0 16px ${markerColor};'></div>`,
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                });
                
                L.marker([{{ $beach->latitude }}, {{ $beach->longitude }}], { icon: icon })
                    .addTo(map)
                    .bindPopup('<strong>{{ $beach->name }}</strong>')
                    .openPopup();
             ">
        </div>
    </div>
</div>
