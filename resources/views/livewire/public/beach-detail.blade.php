<div class="space-y-8" x-data="beachDetailHandler()">
    @section('title', $beach->name . ' - Bandeira e Condições do Mar')

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

    <!-- Beach Header Banner -->
    <div class="glass-card p-6 md:p-8 rounded-3xl flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-3 flex-wrap">
                <span class="text-xs uppercase tracking-widest text-blue-400 bg-blue-500/10 px-3 py-1 rounded-full border border-blue-500/20 font-bold">
                    {{ $beach->region }}
                </span>
                @if($beach->blue_flag)
                    <span class="text-xs uppercase font-bold text-white bg-blue-600 px-2 py-0.5 rounded-md border border-white/10"><span aria-hidden="true">🔷</span> Bandeira Azul</span>
                @endif
                @if($beach->accessible)
                    <span class="text-xs uppercase font-bold text-white bg-teal-600 px-2 py-0.5 rounded-md border border-white/10"><span aria-hidden="true">♿</span> Praia Acessível</span>
                @endif
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-theme tracking-tight mt-2">{{ $beach->name }}</h1>
            <p class="text-slate-400 text-sm mt-1"><span aria-hidden="true">📍</span> {{ $beach->municipality }}, {{ $beach->district ?: $beach->region }}</p>
        </div>

        <div class="flex gap-3">
            <button type="button"
                    wire:click="toggleFavorite"
                    class="px-4 py-2.5 rounded-xl border text-sm font-semibold transition-all flex items-center gap-2 {{ $isFavorited ? 'bg-yellow-500/20 border-yellow-500/40 text-yellow-600' : 'bg-slate-800 hover:bg-slate-700 border-slate-700 text-white hover:text-white' }}"
                    aria-label="{{ $isFavorited ? 'Remover dos favoritos' : 'Adicionar aos favoritos' }}">
                <span>{{ $isFavorited ? '⭐' : '☆' }}</span>
            </button>
            <a href="https://www.google.com/maps/dir/?api=1&destination={{ $beach->latitude }},{{ $beach->longitude }}" 
               target="_blank" 
               class="bg-slate-800 hover:bg-slate-700 text-white px-4 py-2.5 rounded-xl border border-slate-700 text-sm font-semibold transition-all flex items-center gap-2"
               aria-label="Obter direções de GPS para {{ $beach->name }}">
                <span aria-hidden="true">🗺️</span> Direções de GPS
            </a>
        </div>
    </div>

    <!-- Alerts Notification Area -->
    @foreach($alerts as $alert)
        <div class="p-4 rounded-2xl border {{ $alert->type === 'warning' ? 'border-amber-500/30 bg-amber-950/20 text-amber-200' : 'border-rose-500/30 bg-rose-950/20 text-rose-200' }} text-sm leading-relaxed shadow-sm" role="alert">
            <strong class="uppercase font-bold block mb-1"><span aria-hidden="true">⚠️</span> Aviso Oficial:</strong>
            {{ $alert->description }} (Início: {{ $alert->started_at->format('d/m/Y H:i') }})
        </div>
    @endforeach

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Left Column: Flag Card and GPS Reporter (Span 5) -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Active Flag Card -->
            <div class="glass-card p-6 rounded-3xl text-center flex flex-col items-center justify-center relative overflow-hidden">
                <div class="absolute w-48 h-48 rounded-full blur-3xl opacity-20 -top-12 -left-12 bg-blue-500"></div>

                <h2 class="text-xs uppercase tracking-widest text-slate-400 font-bold mb-3">Estado da Bandeira</h2>

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
                     style="box-shadow: 0 12px 48px {{ $glowColor }}"
                     role="img"
                     aria-label="Bandeira {{ $flagName }}">
                    <span class="text-xl font-black uppercase tracking-wider" aria-hidden="true">{{ $flagName }}</span>
                </div>

                <div class="mt-6 space-y-1">
                    <p class="text-sm font-semibold text-slate-200">
                        Origem: 
                        <span class="text-blue-400">
                            {{ $source === 'community' ? 'Confirmação Comunitária' : ($source === 'alert' ? 'Aviso Oficial' : 'Previsão Automática') }}
                        </span>
                    </p>
                    <p class="text-xs text-slate-400">Grau de Confiança: <span class="font-bold text-theme">{{ $confidence }}%</span></p>
                    <p class="text-xs text-slate-500">Última atualização: {{ $beach->currentStatus ? $beach->currentStatus->updated_at->format('H:i') : $beach->updated_at->format('H:i') }}</p>
                </div>

                @if($source === 'prediction' && isset($prediction) && $prediction->selected_flag !== 'gray')
                    <div class="mt-4 w-full max-w-xs space-y-2">
                        <span class="text-xs text-slate-400 uppercase font-bold tracking-wider block">Distribuição de Probabilidades</span>
                        <div class="h-8 w-full rounded-full bg-slate-800/80 flex overflow-hidden shadow-inner border border-theme-subtle p-[2px]">
                            @if($prediction->green_probability > 0)
                                <div class="bg-emerald-500 rounded-l-full transition-all duration-300 flex items-center justify-center text-[16px] font-black text-slate-950" 
                                     style="width: {{ $prediction->green_probability }}%" 
                                     title="Verde: {{ $prediction->green_probability }}%">
                                    {{ $prediction->green_probability }}%
                                </div>
                            @endif
                            @if($prediction->yellow_probability > 0)
                                <div class="bg-amber-500 transition-all duration-300 flex items-center justify-center text-[16px] font-black text-slate-950" 
                                     style="width: {{ $prediction->yellow_probability }}%" 
                                     title="Amarela: {{ $prediction->yellow_probability }}%">
                                    {{ $prediction->yellow_probability }}%
                                </div>
                            @endif
                            @if($prediction->red_probability > 0)
                                <div class="bg-rose-500 rounded-r-full transition-all duration-300 flex items-center justify-center text-[16px] font-black text-white" 
                                     style="width: {{ $prediction->red_probability }}%" 
                                     title="Vermelha: {{ $prediction->red_probability }}%">
                                    {{ $prediction->red_probability }}%
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
                        <span class="text-xs text-slate-400 block leading-tight font-medium">💡 {{ $helperText }}</span>
                    </div>
                @endif

                @if($beach->currentStatus && $beach->currentStatus->reason)
                    <div class="mt-4 p-3 rounded-xl border border-theme-subtle bg-theme-card max-w-xs text-center">
                        <p class="text-xs text-slate-300 font-medium leading-relaxed">
                            <span aria-hidden="true">🔍</span> {{ $beach->currentStatus->reason }}
                        </p>
                    </div>
                @endif
            </div>

            <!-- GPS Confirmation Reporter -->
            <div class="glass-card p-6 rounded-3xl space-y-4">
                <h3 class="text-lg font-bold text-theme flex items-center gap-2">
                    <span aria-hidden="true">📢</span> Confirmar Bandeira no Local
                </h3>
                <p class="text-l text-slate-400 leading-relaxed">
                    Ajuda a comunidade! Se estás fisicamente nesta praia, reporta a cor da bandeira hasteada. A tua localização será validada.
                </p>

                @auth
                    <div aria-live="polite" aria-atomic="true">
                        @if (session()->has('report_success'))
                            <div class="p-3 bg-emerald-500/20 border border-emerald-500/30 text-emerald-200 text-xs rounded-xl font-medium" role="status">
                                <span aria-hidden="true">✔️</span> {{ session('report_success') }}
                            </div>
                        @endif

                        @error('report')
                            <div class="p-3 bg-rose-500/20 border border-rose-500/30 text-rose-200 text-xs rounded-xl font-medium" role="alert">
                                <span aria-hidden="true">❌</span> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div x-show="locating" class="p-3 bg-blue-500/10 border border-blue-500/20 text-blue-300 text-xs rounded-xl flex items-center gap-2" role="status" aria-live="polite">
                        <span class="animate-spin" aria-hidden="true">🌀</span> Obtendo coordenadas GPS precisas...
                    </div>

                    <div class="grid grid-cols-3 gap-3" x-show="!locating" role="group" aria-label="Selecionar cor da bandeira">
                        <button @click="triggerReport('green')" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold py-3 rounded-xl text-xs transition-all shadow shadow-emerald-500/20" aria-label="Reportar bandeira Verde">
                             Verde
                        </button>
                        <button @click="triggerReport('yellow')" class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold py-3 rounded-xl text-xs transition-all shadow shadow-amber-500/20" aria-label="Reportar bandeira Amarela">
                             Amarela
                        </button>
                        <button @click="triggerReport('red')" class="bg-rose-500 hover:bg-rose-400 text-white font-bold py-3 rounded-xl text-xs transition-all shadow shadow-rose-500/20" aria-label="Reportar bandeira Vermelha">
                             Vermelha
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
                    <span class="text-xs text-slate-400 uppercase font-bold block">Ondulação Máxima</span>
                    <span class="text-xl font-bold text-theme block">{{ $ocean && $ocean->wave_height_max !== null ? $ocean->wave_height_max . 'm' : 'Sem Dados' }}</span>
                    @if($ocean && $ocean->wave_direction)
                        <span class="text-xs text-slate-500 block">Dir: {{ $ocean->wave_direction }}</span>
                    @endif
                </div>

                <div class="glass-card p-4 rounded-2xl text-center space-y-1">
                    <span class="text-xs text-slate-400 uppercase font-bold block">Período das Ondas</span>
                    <span class="text-xl font-bold text-theme block">{{ $ocean && $ocean->wave_period_max !== null ? $ocean->wave_period_max . 's' : 'Sem Dados' }}</span>
                    @if($ocean && $ocean->wave_period_min !== null)
                        <span class="text-xs text-slate-500 block">Mín: {{ $ocean->wave_period_min }}s</span>
                    @endif
                </div>

                <div class="glass-card p-4 rounded-2xl text-center space-y-1">
                    <span class="text-xs text-slate-400 uppercase font-bold block">Temp. da Água</span>
                    <span class="text-xl font-bold text-theme block">{{ $ocean && $ocean->water_temp !== null ? $ocean->water_temp . '°C' : 'Sem Dados' }}</span>
                    <span class="text-xs text-slate-500 block">SST Média</span>
                </div>

                <div class="glass-card p-4 rounded-2xl text-center space-y-1">
                    <span class="text-xs text-slate-400 uppercase font-bold block">Intensidade Vento</span>
                    <span class="text-xl font-bold text-theme block">{{ $weather && $weather->wind_speed !== null ? (int)round($weather->wind_speed * 1.852) . ' km/h' : 'Sem Dados' }}</span>
                    @if($weather && $weather->wind_direction)
                        <span class="text-xs text-slate-500 block">Dir: {{ $weather->wind_direction }}</span>
                    @endif
                </div>

                <div class="glass-card p-4 rounded-2xl text-center space-y-1">
                    <span class="text-xs text-slate-400 uppercase font-bold block">Temp. do Ar</span>
                    <span class="text-xl font-bold text-theme block">{{ $weather && $weather->temp !== null ? $weather->temp . '°C' : 'Sem Dados' }}</span>
                    @if($weather && $weather->precipitation !== null)
                        <span class="text-xs text-slate-500 block">Precip: {{ $weather->precipitation }}mm</span>
                    @endif
                </div>

                <div class="glass-card p-4 rounded-2xl text-center space-y-1">
                    <span class="text-xs text-slate-400 uppercase font-bold block">Qualidade Água</span>
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
                    <span class="text-xs text-slate-500 block">Amostra recente</span>
                </div>

                <div class="glass-card p-4 rounded-2xl text-center space-y-1">
                    <span class="text-xs text-slate-400 uppercase font-bold block">Índice UV</span>
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
                    <span class="text-xs text-slate-500 block">{{ $uvLabel }}</span>
                </div>


            </div>

            <!-- Tide Information Card -->
            <div class="glass-card p-6 rounded-3xl space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-theme flex items-center gap-2">
                        🌊 Marés
                    </h3>
                    @if($nextTide)
                        <div class="flex items-center gap-2 text-xs {{ $tideDirection === 'up' ? 'text-sky-400' : 'text-amber-400' }}">
                            <span class="text-lg">{{ $tideDirection === 'up' ? '↑' : '↓' }}</span>
                            <div class="text-right">
                                <span class="font-bold block">{{ $tideDirection === 'up' ? 'A encher' : 'A vazar' }}</span>
                                <span class="text-slate-500">Próx. {{ $nextTide->tide_type === 'high' ? 'Preia-mar' : 'Baixa-mar' }} {{ $nextTide->tide_time->timezone($beach->timezone)->format('H:i') }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                @if($tides->isNotEmpty())
                    @if(!empty($tideCurve))
                        <div class="relative h-20 sm:h-24">
                            <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="w-full h-full">
                                <defs>
                                    <linearGradient id="tideFill" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="rgb(56,189,248)" stop-opacity="0.25"/>
                                        <stop offset="100%" stop-color="rgb(56,189,248)" stop-opacity="0.05"/>
                                    </linearGradient>
                                </defs>
                                <polyline
                                    fill="none"
                                    stroke="rgb(56,189,248)"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    points="{{ trim($tideCurvePoints) }}"
                                />
                                <polygon
                                    fill="url(#tideFill)"
                                    points="0,95 {{ trim($tideCurvePoints) }} 100,95"
                                />
                                @php
                                    $nowPct = 0;
                                    $nowStr = now()->format('H:i');
                                    foreach ($tideCurve as $k => $pt) {
                                        if ($pt['time'] >= $nowStr) { $nowPct = $k / max(count($tideCurve) - 1, 1) * 100; break; }
                                    }
                                @endphp
                                <line x1="{{ $nowPct }}" y1="5" x2="{{ $nowPct }}" y2="95" stroke="rgb(251,191,36)" stroke-width="1.5" stroke-dasharray="2,2"/>
                                <circle cx="{{ $nowPct }}" cy="10" r="2" fill="rgb(251,191,36)"/>
                            </svg>
                        </div>
                    @endif

                    @if($tidesToday->isNotEmpty())
                        <div class="space-y-1.5">
                            <span class="text-xs text-slate-500 font-bold block">Hoje</span>
                            @foreach($tidesToday as $tide)
                                @php
                                    $allHeights = $tides->pluck('tide_height');
                                    $maxH = $allHeights->max();
                                    $minH = $allHeights->min();
                                    $rangeH = max($maxH - $minH, 0.5);
                                    $barPct = (($tide->tide_height - $minH) / $rangeH) * 100;
                                    $isPast = $tide->tide_time->isPast();
                                @endphp
                                <div class="flex items-center gap-3 py-1.5 {{ $isPast ? 'opacity-50' : '' }}">
                                    <span class="text-xs text-slate-500 w-10 tabular-nums shrink-0">{{ $tide->tide_time->timezone($beach->timezone)->format('H:i') }}</span>
                                    <div class="flex-1 h-5 bg-theme-card border border-theme-subtle rounded-full overflow-hidden relative">
                                        <div class="h-full rounded-full {{ $tide->tide_type === 'high' ? 'bg-gradient-to-r from-sky-500/60 to-blue-500/60' : 'bg-gradient-to-r from-amber-500/40 to-yellow-600/40' }}" style="width: {{ max($barPct, 5) }}%"></div>
                                        <span class="absolute inset-0 flex items-center px-2 text-xs font-bold tabular-nums {{ $tide->tide_height > 2 ? 'text-white' : 'text-slate-300' }}">{{ $tide->tide_height }}m</span>
                                    </div>
                                    <span class="text-xs font-bold text-theme w-16 text-right shrink-0">{{ $tide->tide_type === 'high' ? 'Preia-mar' : 'Baixa-mar' }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if($tidesTomorrow->isNotEmpty())
                        <details class="group">
                            <summary class="text-xs text-slate-500 font-bold cursor-pointer list-none flex items-center gap-2 hover:text-slate-300 transition-colors">
                                <svg class="w-3 h-3 group-open:rotate-90 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                Amanhã ({{ $tidesTomorrow->count() }} marés)
                            </summary>
                            <div class="space-y-1.5 pt-2">
                                @foreach($tidesTomorrow as $tide)
                                    @php
                                        $barPct = (($tide->tide_height - $minH) / $rangeH) * 100;
                                    @endphp
                                    <div class="flex items-center gap-3 py-1.5">
                                        <span class="text-xs text-slate-500 w-10 tabular-nums shrink-0">{{ $tide->tide_time->timezone($beach->timezone)->format('H:i') }}</span>
                                        <div class="flex-1 h-5 bg-theme-card border border-theme-subtle rounded-full overflow-hidden relative">
                                            <div class="h-full rounded-full {{ $tide->tide_type === 'high' ? 'bg-gradient-to-r from-sky-500/60 to-blue-500/60' : 'bg-gradient-to-r from-amber-500/40 to-yellow-600/40' }}" style="width: {{ max($barPct, 5) }}%"></div>
                                            <span class="absolute inset-0 flex items-center px-2 text-xs font-bold tabular-nums {{ $tide->tide_height > 2 ? 'text-white' : 'text-slate-300' }}">{{ $tide->tide_height }}m</span>
                                        </div>
                                        <span class="text-xs font-bold text-theme w-16 text-right shrink-0">{{ $tide->tide_type === 'high' ? 'Preia-mar' : 'Baixa-mar' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </details>
                    @endif

                    <div class="pt-3 border-t border-theme-subtle grid grid-cols-2 gap-3 text-xs">
                        @if($moonPhase !== null)
                            <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-theme-card border border-theme-subtle">
                                <span class="text-xl">{{ \App\Models\TideForecast::moonPhaseIcon($moonPhase) }}</span>
                                <div>
                                    <span class="font-bold text-theme block leading-tight">{{ \App\Models\TideForecast::moonPhaseName($moonPhase) }}</span>
                                    <span class="text-slate-500">Fase lunar</span>
                                </div>
                            </div>
                        @endif
                        @if($nextTide)
                            <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-theme-card border border-theme-subtle">
                                <span class="text-xl {{ $tideDirection === 'up' ? 'text-sky-400' : 'text-amber-400' }}">{{ $tideDirection === 'up' ? '⬆' : '⬇' }}</span>
                                <div>
                                    <span class="font-bold text-theme block leading-tight">{{ $nextTide->tide_type === 'high' ? 'Preia-mar' : 'Baixa-mar' }}</span>
                                    <span class="text-slate-500">{{ $nextTide->tide_time->timezone($beach->timezone)->format('H:i') }}</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if(count($upcomingMoonPhases) > 0)
                        <div class="pt-2 border-t border-theme-subtle">
                            <span class="text-xs text-slate-500 font-bold block pb-2">Próximas fases lunares</span>
                            <div class="grid grid-cols-4 gap-2 text-xs">
                                @foreach($upcomingMoonPhases as $item)
                                    <div class="p-2 rounded-xl bg-theme-card border border-theme-subtle text-center">
                                        <span class="text-lg block">{{ $item['icon'] }}</span>
                                        <span class="font-bold text-theme block leading-tight mt-0.5">{{ $item['name'] }}</span>
                                        <span class="text-slate-500">{{ $item['date']->format('d/m') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @else
                    <p class="text-xs text-slate-500">Sem previsões de marés disponíveis.</p>
                @endif
            </div>

            <!-- Description & Services -->
            <div class="glass-card p-6 rounded-3xl space-y-6">
                <div class="space-y-2">
                    <h3 class="text-lg font-bold text-theme">Sobre a Praia</h3>
                    <p class="text-sm text-slate-300 leading-relaxed">
                        {{ $beach->description ?: 'Esta praia oficial vigiada apresenta uma excelente época balnear e águas com classificações periódicas ótimas.' }}
                    </p>
                </div>

                <div class="space-y-3">
                    <h3 class="text-sm uppercase tracking-wide text-slate-400 font-bold">Serviços Disponíveis</h3>
                    @if($beach->services)
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs text-theme">
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
                                    <div class="bg-theme-card border border-theme-subtle px-2.5 py-2 rounded-lg flex items-center gap-1.5 font-medium">
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
                <h3 class="text-lg font-bold text-theme flex items-center gap-2">
                    🍴 Onde Comer por Perto
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($beach->restaurants as $restaurant)
                        <div class="glass-card p-4 rounded-2xl flex flex-col justify-between gap-3 relative group">
                            <span class="absolute top-3 right-3 text-xs uppercase font-bold tracking-wider px-2 py-0.5 rounded-full {{ $restaurant->source === 'tripadvisor' ? 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-600 border border-amber-500/20' }}">
                                {{ $restaurant->source }}
                            </span>

                            <div class="space-y-1">
                                <h4 class="font-bold text-theme group-hover:text-blue-400 transition-colors text-sm pr-16">{{ $restaurant->name }}</h4>
                                <p class="text-xs text-slate-400">{{ $restaurant->cuisine_type }}</p>
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="text-yellow-400">★ {{ $restaurant->rating }}</span>
                                    <span class="text-slate-500">({{ $restaurant->reviews_count }} avaliações)</span>
                                </div>
                                @if($restaurant->average_price)
                                    <p class="text-xs text-slate-300">Preço Médio: <span class="font-bold text-theme">{{ $restaurant->average_price }} €</span></p>
                                @endif
                                <p class="text-xs text-slate-500">Distância da praia: {{ round($restaurant->pivot->distance, 2) }} km</p>
                            </div>

                            <div class="flex gap-2 pt-2 border-t border-theme-subtle">
                                @if($restaurant->booking_url)
                                    <a href="{{ $restaurant->booking_url }}" target="_blank" class="flex-1 text-center bg-blue-600 hover:bg-blue-500 text-white text-[11px] font-bold py-1.5 rounded-lg transition-colors" aria-label="Reservar mesa no {{ $restaurant->name }}">
                                        Reservar Mesa
                                    </a>
                                @endif
                                <a href="{{ $restaurant->external_url }}" target="_blank" class="flex-1 text-center bg-slate-800 hover:bg-slate-700 text-white text-[11px] font-bold py-1.5 rounded-lg transition-colors border border-slate-700" aria-label="Ver ficha do {{ $restaurant->name }}">
                                    Ver Ficha
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 glass-card p-6 rounded-xl border border-theme-medium text-center text-theme-muted text-xs">
                            Sem recomendações de restaurantes disponíveis na cache do TripAdvisor ou TheFork para esta praia.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet single location map section -->
    <div class="glass-card p-4 rounded-3xl border border-theme-medium">
        <h3 class="text-sm font-bold text-theme uppercase tracking-wider mb-3"><span aria-hidden="true">📍</span> Localização Geográfica</h3>
        <div id="beach-map"
             data-lat="{{ $beach->latitude }}"
             data-lng="{{ $beach->longitude }}"
             data-color="{{ $markerColorHex }}"
             data-name="{{ $beach->name }}"
             class="w-full h-80 rounded-2xl border border-theme-subtle overflow-hidden z-0"
             role="application"
             aria-label="Mapa de localização da {{ $beach->name }}">
        </div>
    </div>

    @script
        <script>
            Alpine.data('beachDetailHandler', () => ({
                locating: false,
                mapInstance: null,
                mapReady: false,

                init() {
                    this.$nextTick(() => this.loadMap());
                },

                loadMap() {
                    const el = document.getElementById('beach-map');
                    if (!el || typeof L === 'undefined') {
                        setTimeout(() => this.loadMap(), 100);
                        return;
                    }
                    const lat = parseFloat(el.dataset.lat);
                    const lng = parseFloat(el.dataset.lng);
                    const color = el.dataset.color;
                    const name = el.dataset.name;
                    if (isNaN(lat) || isNaN(lng)) return;

                    if (this.mapInstance) {
                        this.mapInstance.remove();
                        this.mapInstance = null;
                    }

                    this.mapInstance = L.map(el, {
                        zoomControl: true,
                        scrollWheelZoom: true
                    }).setView([lat, lng], 14);

                    this.mapInstance.on('popupopen', function(e) {
                        const closeBtn = e.popup._container.querySelector('.leaflet-popup-close-button');
                        if (closeBtn) {
                            closeBtn.removeAttribute('href');
                            closeBtn.setAttribute('role', 'button');
                        }
                    });

                    let tileCounter = 0;
                    const layer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                        maxZoom: 19,
                        alt: 'Mapa'
                    });
                    layer.on('tileload', function(e) {
                        if (e.tile) {
                            tileCounter++;
                            e.tile.setAttribute('role', 'presentation');
                            e.tile.setAttribute('aria-hidden', 'true');
                            e.tile.setAttribute('alt', `Mapa Bloco ${tileCounter}`);
                        }
                    });
                    layer.addTo(this.mapInstance);

                    const isDark = document.documentElement.getAttribute('data-theme') !== 'light';
                    const markerBorder = isDark ? '#070a13' : '#ffffff';
                    const icon = L.divIcon({
                        className: 'detail-div-icon',
                        html: `<div style="width:20px;height:20px;background:${color};border:3.5px solid ${markerBorder};border-radius:50%;box-shadow:0 0 16px ${color};"></div>`,
                        iconSize: [20, 20],
                        iconAnchor: [10, 10]
                    });
                    const marker = L.marker([lat, lng], { icon })
                        .addTo(this.mapInstance)
                        .bindPopup('<strong>' + name + '</strong>')
                        .openPopup();
                    const markerEl = marker.getElement();
                    if (markerEl) {
                        markerEl.setAttribute('aria-label', `Marcador de ${name}`);
                    }

                    this.mapReady = true;
                },

                triggerReport(flagColor) {
                    this.locating = true;
                    if (!navigator.geolocation) {
                        this.$dispatch('notify', { message: 'O teu navegador não suporta geolocalização.', type: 'error' });
                        this.locating = false;
                        return;
                    }
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            $wire.call('submitReport', flagColor, position.coords.latitude, position.coords.longitude, position.coords.accuracy);
                            this.locating = false;
                        },
                        (error) => {
                            this.$dispatch('notify', { message: 'Erro de GPS: A permissão de localização é obrigatória para confirmar a bandeira.', type: 'error' });
                            this.locating = false;
                        },
                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                    );
                }
            }));
        </script>
    @endscript
</div>
