<div class="space-y-8" x-data="beachDetailHandler()" @if(auth()->check() && auth()->user()->is_admin) data-is-admin="1" @endif>

@pushOnce('leaflet-css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.css" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.css"></noscript>
@endPushOnce

@pushOnce('leaflet-js')
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.js" defer></script>
@endPushOnce

    @section('title', $beach->name . ' - ' . __('common.site_name'))
    @section('meta_description', __('beach.meta_description', ['name' => $beach->name, 'municipality' => $beach->municipality]))
    @section('og_title', $beach->name . ' - Bandeira e Condições do Mar')
    @section('og_description', strip_tags(__('beach.meta_description', ['name' => $beach->name, 'municipality' => $beach->municipality])))
    @section('og_type', 'place')
    @section('canonical', $beach->url)
    @section('hreflang')
        @foreach(config('locales.supported', ['pt', 'en', 'es', 'fr']) as $locale)
            @php $routeName = 'beach.show.' . $locale; @endphp
            @if(Route::has($routeName))
                <link rel="alternate" hreflang="{{ $locale }}" href="{{ route($routeName, ['locale' => $locale, 'slug' => $beach->slug]) }}">
            @endif
        @endforeach
    @endsection
    @section('ld_json')
    @parent
    <script type="application/ld+json">
    @php echo json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        '@id' => $beach->url . '#breadcrumb',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => __('home.page_title'), 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $beach->name, 'item' => $beach->url],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); @endphp
    </script>
    <script type="application/ld+json">
    @php echo json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Beach',
        '@id' => $beach->url . '#beach',
        'name' => $beach->name,
        'description' => strip_tags(__('beach.meta_description', ['name' => $beach->name, 'municipality' => $beach->municipality])),
        'url' => $beach->url,
        'containedInPlace' => [
            '@type' => 'AdministrativeArea',
            'name' => $beach->municipality,
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => $beach->municipality,
                'addressCountry' => 'PT',
            ],
        ],
        'latitude' => (float) $beach->latitude,
        'longitude' => (float) $beach->longitude,
        'image' => asset('logo.png'),
        'isAccessibleForFree' => true,
        'publicAccess' => true,
        'openingHours' => 'Mo-Su 09:00-19:00',
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); @endphp
    </script>
    @endsection

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

    <!-- Beach Header Banner -->
    <nav aria-label="Breadcrumb" class="mb-3 text-xs text-theme-muted">
        <ol class="flex items-center gap-1.5">
            <li><a href="{{ route('home') }}" wire:navigate class="hover:text-blue-400 transition-colors">{{ __('home.page_title') ?? 'Início' }}</a></li>
            <li aria-hidden="true" class="opacity-50">›</li>
            <li aria-current="page" class="text-theme font-medium">{{ $beach->name }}</li>
        </ol>
    </nav>
    <div class="glass-card p-6 md:p-8 rounded-3xl flex flex-col md:flex-row justify-between items-start md:items-center gap-4 relative overflow-hidden border border-theme-subtle/50 shadow-xl shadow-black/[0.02] animate-fade-in-up" data-animate>
        <div class="absolute inset-0 bg-gradient-to-r from-blue-600/10 via-indigo-600/5 to-transparent pointer-events-none"></div>
        <div class="absolute -right-24 -top-24 w-96 h-96 rounded-full bg-blue-500/10 blur-3xl pointer-events-none animate-float"></div>
        <div class="absolute -left-24 -bottom-24 w-96 h-96 rounded-full bg-indigo-500/5 blur-3xl pointer-events-none animate-float" style="animation-delay: 1s;"></div>

        <div class="relative z-10">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-[11px] uppercase tracking-widest text-blue-400 bg-blue-500/10 px-3 py-1 rounded-full border border-blue-500/20 font-extrabold shadow-sm">
                    {{ $beach->region }}
                </span>
                @if($beach->blue_flag)
                    <span class="text-[11px] uppercase tracking-wider font-extrabold text-white bg-blue-600/80 px-2.5 py-1 rounded-md border border-white/10 flex items-center gap-1 shadow-sm">
                        <span>🔷</span> {{ __('common.flag_blue_flag') }}
                    </span>
                @endif
                @if($beach->accessible)
                    <span class="text-[11px] uppercase tracking-wider font-extrabold text-white bg-teal-600/80 px-2.5 py-1 rounded-md border border-white/10 flex items-center gap-1 shadow-sm">
                        <span>♿</span> {{ __('common.flag_accessible') }}
                    </span>
                @endif
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-theme tracking-tight mt-2.5">{{ $beach->name }}</h1>
            <p class="text-slate-400 text-sm mt-1.5 flex items-center gap-1.5 font-medium">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <circle cx="12" cy="11" r="2" stroke="currentColor" stroke-width="2"/>
                </svg>
                <span>{{ $beach->municipality }}, {{ $beach->district ?: $beach->region }}</span>
            </p>
        </div>

        <div class="flex gap-2 sm:gap-3 relative z-10 w-full md:w-auto">
            <button type="button"
                    wire:click="toggleFavorite"
                    class="flex-1 md:flex-none justify-center px-3 sm:px-5 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl border text-xs sm:text-sm font-bold transition-all active:scale-90 flex items-center gap-1.5 sm:gap-2 {{ $isFavorited ? 'bg-amber-500/10 border-amber-500/30 text-amber-400 shadow-sm shadow-amber-500/5' : 'bg-slate-800/80 hover:bg-slate-700/80 border-slate-700/60 text-slate-300 hover:text-white' }}"
                    aria-label="{{ $isFavorited ? __('common.favorite_remove') : __('common.favorite_add') }}">
                <span class="text-base sm:text-sm">{{ $isFavorited ? '★' : '☆' }}</span>
                <span class="hidden sm:inline">{{ $isFavorited ? __('common.favorite_remove') : __('common.favorite_add') }}</span>
            </button>
            @if($beach->beachcam_slug)
                <a href="https://beachcam.meo.pt/livecams/{{ $beach->beachcam_slug }}/"
                   target="_blank"
                   rel="noopener"
                   class="flex-1 md:flex-none justify-center bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white px-3 sm:px-5 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl border border-emerald-500/30 text-xs sm:text-sm font-bold transition-all active:scale-90 flex items-center justify-center gap-1.5 sm:gap-2 shadow-lg shadow-emerald-500/10 hover:shadow-xl hover:shadow-emerald-500/20"
                   aria-label="Ver {{ $beach->name }} ao vivo">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14m0-4v4m0 0H5a2 2 0 01-2-2V8a2 2 0 012-2h10a2 2 0 012 2v4z"/>
                    </svg>
                    <span class="hidden sm:inline">{{ __('common.live') }}</span>
                </a>
            @endif
            <a href="https://www.google.com/maps/dir/?api=1&destination={{ $beach->latitude }},{{ $beach->longitude }}" 
               target="_blank" 
               class="flex-1 md:flex-none justify-center bg-blue-600 hover:bg-blue-500 text-white px-3 sm:px-5 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl border border-blue-500/30 text-xs sm:text-sm font-bold transition-all active:scale-90 flex items-center justify-center gap-1.5 sm:gap-2 shadow-lg shadow-blue-500/10 hover:shadow-xl hover:shadow-blue-500/20"
               aria-label="{{ __('beach.gps_button') }} {{ $beach->name }}">
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
                <span class="hidden sm:inline">{{ __('beach.gps_button') }}</span>
            </a>
        </div>
    </div>

    <!-- Alerts Notification Area -->
    @foreach($alerts as $alert)
        <div class="p-4 rounded-3xl border {{ $alert->type === 'warning' ? 'border-amber-500/30 bg-amber-950/20 text-amber-200 shadow-amber-500/5' : 'border-rose-500/30 bg-rose-950/20 text-rose-200 shadow-rose-500/5' }} text-sm leading-relaxed shadow-md flex gap-3 items-start animate-fade-in-up" role="alert" data-animate>
            <svg class="w-5 h-5 shrink-0 mt-0.5 {{ $alert->type === 'warning' ? 'text-amber-400' : 'text-rose-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <strong class="uppercase font-extrabold block text-xs tracking-wider mb-0.5">{{ __('common.footer_disclaimer_title') }}:</strong>
                {{ $alert->description }} ({{ __('beach.start_label', ['date' => $alert->started_at->format('d/m/Y H:i')]) }})
            </div>
        </div>
    @endforeach

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Left Column: Flag Card and GPS Reporter (Span 5) -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Active Flag Card -->
            <div class="glass-card p-6 rounded-3xl text-center flex flex-col items-center justify-center relative overflow-hidden border border-theme-subtle/50 animate-fade-in-up" data-animate>
                <div class="absolute w-48 h-48 rounded-full blur-3xl opacity-15 -top-12 -left-12 bg-blue-500 animate-float"></div>

                <h2 class="text-[11px] uppercase tracking-widest text-slate-400 font-extrabold mb-4">{{ __('beach.flag_title') }}</h2>

                @php
                    $flag = $beach->getDisplayFlag();
                    $source = $beach->currentStatus ? $beach->currentStatus->source : 'prediction';
                    $confidence = $beach->currentStatus ? $beach->currentStatus->confidence : 100;
                    $flagName = match($flag) {
                        'green' => __('common.flag_green'),
                        'yellow' => __('common.flag_yellow'),
                        'red' => __('common.flag_red'),
                        'blue_or_neutral' => __('common.flag_blue_or_neutral'),
                        default => __('common.flag_none')
                    };
                    $glowColor = match($flag) {
                        'green' => 'rgba(16, 185, 129, 0.35)',
                        'yellow' => 'rgba(245, 158, 11, 0.35)',
                        'red' => 'rgba(239, 68, 68, 0.35)',
                        'blue_or_neutral' => 'rgba(59, 130, 246, 0.35)',
                        default => 'rgba(107, 114, 128, 0.35)'
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

                <!-- Dynamic Glowing Flag Circle Container -->
                <div class="relative flex items-center justify-center my-2 group">
                    <div class="absolute inset-0 rounded-full animate-ping opacity-25" style="background-color: {{ $markerColorHex }}; transform: scale(1.05);"></div>
                    <div class="absolute inset-0 rounded-full animate-pulse-glow" style="--glow-color: {{ $glowColor }}; transform: scale(1.1);"></div>
                    <div class="w-32 h-32 rounded-full flex items-center justify-center shadow-2xl transition-all duration-500 relative z-10 {{ $flagBg }}" 
                         style="box-shadow: 0 16px 48px {{ $glowColor }}"
                         role="img"
                         aria-label="Bandeira {{ $flagName }}">
                        <span class="text-xl font-black uppercase tracking-wider">{{ $flagName }}</span>
                    </div>
                </div>

                @if($flag === 'gray')
                    <div class="mt-6 p-4 rounded-2xl border border-amber-500/20 bg-amber-500/5 max-w-xs text-center animate-fade-in">
                        <p class="text-xs text-amber-400 font-bold leading-relaxed">
                            ⚠️ {{ __('beach.no_lifeguards_warning', ['start' => \Carbon\Carbon::parse($beach->lifeguard_start)->format('H:i'), 'end' => \Carbon\Carbon::parse($beach->lifeguard_end)->format('H:i')]) }}
                        </p>
                    </div>
                @else
                    <div class="mt-6 space-y-1.5">
                        <p class="text-sm font-bold text-slate-200">
                            {{ __('common.flag_prediction') }}: 
                            <span class="text-blue-400">
                                {{ $source === 'community' ? __('beach.flag_community') : ($source === 'alert' ? __('beach.flag_official') : __('beach.flag_predicted')) }}
                            </span>
                        </p>
                        <div class="flex items-center justify-center gap-3">
                            <span class="text-xs text-slate-400">{{ __('common.flag_confirmed') }}: <span class="font-extrabold text-theme">{{ $confidence }}%</span></span>
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-600"></span>
                            <span class="text-xs text-slate-400">{{ __('common.weather_forecast') }}: <span class="font-bold text-theme">{{ $beach->currentStatus ? $beach->currentStatus->updated_at->timezone($beach->timezone)->format('H:i') : $beach->updated_at->timezone($beach->timezone)->format('H:i') }}</span></span>
                        </div>
                    </div>

                    @if($source === 'prediction' && isset($prediction) && $prediction->selected_flag !== 'gray')
                        <div class="mt-5 w-full max-w-xs space-y-2">
                            <span class="text-[11px] text-slate-400 uppercase font-extrabold tracking-wider block">{{ __('beach.weather_title') }}</span>
                            <div class="h-8 w-full rounded-full bg-slate-800/80 flex overflow-hidden shadow-inner border border-theme-subtle p-[3px]">
                                @if($prediction->green_probability > 0)
                                    <div class="bg-emerald-500 rounded-l-full transition-all duration-500 flex items-center justify-center text-xs font-black text-slate-950" 
                                         style="width: {{ $prediction->green_probability }}%" 
                                         title="{{ __('common.flag_green') }}: {{ $prediction->green_probability }}%">
                                        {{ $prediction->green_probability }}%
                                    </div>
                                @endif
                                @if($prediction->yellow_probability > 0)
                                    <div class="bg-amber-500 transition-all duration-500 flex items-center justify-center text-xs font-black text-slate-950" 
                                         style="width: {{ $prediction->yellow_probability }}%" 
                                         title="{{ __('common.flag_yellow') }}: {{ $prediction->yellow_probability }}%">
                                        {{ $prediction->yellow_probability }}%
                                    </div>
                                @endif
                                @if($prediction->red_probability > 0)
                                    <div class="bg-rose-500 rounded-r-full transition-all duration-500 flex items-center justify-center text-xs font-black text-white" 
                                         style="width: {{ $prediction->red_probability }}%" 
                                         title="{{ __('common.flag_red') }}: {{ $prediction->red_probability }}%">
                                        {{ $prediction->red_probability }}%
                                    </div>
                                @endif
                            </div>
                            
                            @php
                                $g = $prediction->green_probability;
                                $y = $prediction->yellow_probability;
                                $r = $prediction->red_probability;
                                $helperText = __('beach.helper_stable');
                                if ($g >= 30 && $y >= 30) {
                                    $helperText = __('beach.helper_mixed_green_yellow');
                                } elseif ($y >= 30 && $r >= 30) {
                                    $helperText = __('beach.helper_mixed_yellow_red');
                                } elseif ($g >= 30 && $r >= 30) {
                                    $helperText = __('beach.helper_volatile');
                                }
                            @endphp
                            <span class="text-[11px] text-slate-400 block leading-tight font-medium">💡 {{ $helperText }}</span>
                        </div>
                    @endif
                @endif

                @if($beach->currentStatus && $beach->currentStatus->reason)
                    <div class="mt-4 p-3 rounded-2xl border border-theme-subtle bg-theme-card max-w-xs text-center">
                        <p class="text-xs text-slate-300 font-medium leading-relaxed">
                            <span aria-hidden="true">🔍</span> {{ $beach->currentStatus->reason }}
                        </p>
                    </div>
                @endif
            </div>

            <!-- GPS Confirmation Reporter -->
            @php $isAdmin = auth()->check() && auth()->user()->is_admin; @endphp
            <div class="glass-card p-6 rounded-3xl space-y-4 border border-theme-subtle/50 shadow-md animate-fade-in-up" data-animate>
                <h3 class="text-lg font-bold text-theme flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    <span>{{ __('beach.report_title') }}</span>
                    @if($isAdmin)
                        <span class="text-[10px] uppercase tracking-wider px-2 py-0.5 bg-purple-500/15 text-purple-300 border border-purple-500/20 rounded-full font-bold">Admin</span>
                    @endif
                </h3>
                @if(!$beach->isInLifeguardHours() && !$isAdmin)
                    <p class="text-sm text-amber-400 leading-relaxed font-bold bg-amber-500/5 border border-amber-500/10 p-3.5 rounded-2xl text-center">
                        🔒 {{ __('beach.report_outside_lifeguard_hours', ['start' => \Carbon\Carbon::parse($beach->lifeguard_start)->format('H:i'), 'end' => \Carbon\Carbon::parse($beach->lifeguard_end)->format('H:i')]) }}
                    </p>
                @else
                    @if($isAdmin && !$beach->isInLifeguardHours())
                        <p class="text-sm text-purple-300 leading-relaxed font-medium bg-purple-500/5 border border-purple-500/10 p-3 rounded-2xl text-center">
                            ⚡ {{ __('beach.admin_override_active') }}
                        </p>
                    @else
                        <p class="text-sm text-slate-400 leading-relaxed font-medium">
                            {{ __('beach.report_description') }}
                        </p>
                    @endif

                    @auth
                        <div aria-live="polite" aria-atomic="true">
                            @if (session()->has('report_success'))
                                <div class="p-3 bg-emerald-500/20 border border-emerald-500/30 text-emerald-200 text-xs rounded-xl font-medium animate-fade-in" role="status">
                                    ✔️ {{ session('report_success') }}
                                </div>
                            @endif

                            @error('report')
                                <div class="p-3 bg-rose-500/20 border border-rose-500/30 text-rose-200 text-xs rounded-xl font-medium animate-fade-in" role="alert">
                                    ❌ {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div x-show="locating" class="p-3 bg-blue-500/10 border border-blue-500/20 text-blue-300 text-xs rounded-2xl flex items-center gap-2.5 animate-fade-in" role="status" aria-live="polite">
                            <span class="animate-spin text-lg" aria-hidden="true">🌀</span>
                            <span>{{ __('common.search_nearby') }}...</span>
                        </div>

                        <div class="grid grid-cols-3 gap-2.5" x-show="!locating" role="group" aria-label="{{ __('beach.select_flag') }}">
                            <button @click="triggerReport('green')" class="bg-emerald-500 hover:bg-emerald-400 active:scale-90 text-slate-950 font-bold py-3.5 rounded-2xl text-xs transition-all shadow-md shadow-emerald-500/10 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-emerald-500/20" aria-label="{{ __('beach.report_confirm_green') }}">
                                🟢 {{ __('common.flag_green') }}
                            </button>
                            <button @click="triggerReport('yellow')" class="bg-amber-500 hover:bg-amber-400 active:scale-90 text-slate-950 font-bold py-3.5 rounded-2xl text-xs transition-all shadow-md shadow-amber-500/10 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-amber-500/20" aria-label="{{ __('beach.report_confirm_yellow') }}">
                                🟡 {{ __('common.flag_yellow') }}
                            </button>
                            <button @click="triggerReport('red')" class="bg-rose-500 hover:bg-rose-400 active:scale-90 text-white font-bold py-3.5 rounded-2xl text-xs transition-all shadow-md shadow-rose-500/10 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-rose-500/20" aria-label="{{ __('beach.report_confirm_red') }}">
                                🔴 {{ __('common.flag_red') }}
                            </button>
                        </div>
                    @else
                        <div class="p-4 bg-slate-800/80 rounded-2xl border border-slate-700/60 text-center text-xs text-slate-400 font-medium">
                            {{ __('common.favorite_login_required') }}
                            <a href="{{ route('profile') }}" class="text-blue-400 hover:underline font-bold">{{ __('common.nav_login') }}</a>.
                        </div>
                    @endauth
                @endif
            </div>

            @if($todayReports && $todayReports->isNotEmpty())
                <div class="glass-card p-5 rounded-3xl border border-theme-subtle/50 space-y-3 animate-fade-in-up" data-animate>
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-theme flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ __('beach.today_votes_title') }}</span>
                        </h3>
                        @php
                            $voteCounts = $todayReports->groupBy('flag')->map->count();
                            $consensus = $beach->currentStatus && $beach->currentStatus->source === 'community' ? $beach->currentStatus->flag : null;
                        @endphp
                        @if($consensus)
                            @php
                                $consensusLabel = match($consensus) {
                                    'green' => __('common.flag_green'),
                                    'yellow' => __('common.flag_yellow'),
                                    'red' => __('common.flag_red'),
                                    default => ''
                                };
                                $consensusColor = match($consensus) {
                                    'green' => 'text-emerald-400',
                                    'yellow' => 'text-amber-400',
                                    'red' => 'text-rose-400',
                                    default => 'text-slate-400'
                                };
                            @endphp
                            <span class="text-xs font-extrabold uppercase tracking-wider {{ $consensusColor }}">
                                {{ $consensusLabel }}
                            </span>
                        @endif
                    </div>

                    <!-- Vote count bars -->
                    <div class="flex gap-2 h-2">
                        @php
                            $total = $todayReports->count();
                            $greenCount = $voteCounts->get('green', 0);
                            $yellowCount = $voteCounts->get('yellow', 0);
                            $redCount = $voteCounts->get('red', 0);
                        @endphp
                        @if($greenCount > 0)
                            <div class="bg-emerald-500 rounded-full transition-all" style="width: {{ ($greenCount / $total) * 100 }}%" title="{{ __('common.flag_green') }}: {{ $greenCount }}"></div>
                        @endif
                        @if($yellowCount > 0)
                            <div class="bg-amber-500 rounded-full transition-all" style="width: {{ ($yellowCount / $total) * 100 }}%" title="{{ __('common.flag_yellow') }}: {{ $yellowCount }}"></div>
                        @endif
                        @if($redCount > 0)
                            <div class="bg-rose-500 rounded-full transition-all" style="width: {{ ($redCount / $total) * 100 }}%" title="{{ __('common.flag_red') }}: {{ $redCount }}"></div>
                        @endif
                    </div>

                    <!-- Vote entries -->
                    <div class="space-y-1.5 max-h-48 overflow-y-auto scrollbar-none pr-1" data-animate-stagger="0.04">
                        @foreach($todayReports as $report)
                            @php
                                $voteFlagLabel = match($report->flag) {
                                    'green' => __('common.flag_green'),
                                    'yellow' => __('common.flag_yellow'),
                                    'red' => __('common.flag_red'),
                                    default => ''
                                };
                                $voteFlagColor = match($report->flag) {
                                    'green' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/20',
                                    'yellow' => 'bg-amber-500/15 text-amber-400 border-amber-500/20',
                                    'red' => 'bg-rose-500/15 text-rose-400 border-rose-500/20',
                                    default => 'bg-slate-500/15 text-slate-400 border-slate-500/20'
                                };
                                $isCurrentUser = auth()->check() && $report->user_id === auth()->id();
                            @endphp
                            <div class="flex items-center justify-between gap-2 py-1.5 px-2 rounded-xl transition-colors {{ $isCurrentUser ? 'bg-blue-500/5 border border-blue-500/10' : 'hover:bg-white/[0.02]' }}">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-6 h-6 rounded-full bg-theme-card border border-theme-subtle flex items-center justify-center text-[11px] font-bold text-theme-secondary shrink-0">
                                        {{ strtoupper(substr($report->user->name ?? '?', 0, 1)) }}
                                    </span>
                                    <span class="text-xs font-semibold text-theme truncate {{ $isCurrentUser ? 'text-blue-400' : '' }}">
                                        {{ $report->user->name ?? __('common.anonymous') }}
                                        @if($isCurrentUser)
                                            <span class="text-[11px] text-blue-400 font-bold">(tu)</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="text-[11px] text-theme-muted font-medium tabular-nums">{{ $report->reported_at->timezone($beach->timezone)->format('H:i') }}</span>
                                    <span class="text-[11px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded border {{ $voteFlagColor }}">
                                        {{ $voteFlagLabel }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($todaySnapshots && $todaySnapshots->isNotEmpty())
                @php $totalSnapshots = $todaySnapshots->count(); @endphp
                <div class="glass-card p-5 rounded-3xl border border-theme-subtle/50 space-y-3 animate-fade-in-up" data-animate x-data="{ expanded: false }">
                    <h3 class="text-sm font-bold text-theme flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ __('beach.history_title') }}</span>
                        <span class="text-[10px] text-theme-muted font-normal ml-auto">{{ $totalSnapshots }} {{ __('beach.history_entries') }}</span>
                    </h3>

                    <div class="relative pl-6 space-y-0 overflow-visible">
                        @php
                            $prevFlag = null;
                            $flagColors = [
                                'green' => 'bg-emerald-500',
                                'yellow' => 'bg-amber-500',
                                'red' => 'bg-rose-500',
                                'blue_or_neutral' => 'bg-blue-600',
                                'gray' => 'bg-slate-500',
                            ];
                        @endphp
                        @foreach($todaySnapshots as $snapshot)
                            @php
                                $isWithinHours = $snapshot->within_hours ?? true;
                                $isAdmin = auth()->check() && auth()->user()->is_admin;
                                $displayFlag = ($isWithinHours || $isAdmin) ? $snapshot->flag : 'gray';

                                $dotColor = $flagColors[$displayFlag] ?? 'bg-slate-500';
                                $changed = $prevFlag && $prevFlag !== $displayFlag;
                                $prevFlag = $displayFlag;
                                $displayTime = $snapshot->vote_time 
                                    ? $snapshot->vote_time->timezone($beach->timezone) 
                                    : $snapshot->captured_at->timezone($beach->timezone);
                                $isHidden = $loop->index >= 6;
                            @endphp
                            <div class="relative pb-3 {{ !$loop->last ? 'border-l-2 border-theme-subtle/30' : '' }} pl-4 ml-[-1px]">
                                @if($changed)
                                    <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full {{ $dotColor }} ring-2 ring-theme-card ring-offset-2 ring-offset-theme-card shadow-lg animate-fade-in"></div>
                                @else
                                    <div class="absolute -left-[7px] top-1.5 w-3 h-3 rounded-full {{ $dotColor }} ring-2 ring-theme-card opacity-60"></div>
                                @endif
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[11px] text-theme-muted font-medium tabular-nums">
                                            {{ $displayTime->format('H:i') }}
                                        </span>
                                        @if($snapshot->source === 'community' && ($isWithinHours || $isAdmin))
                                            <span class="text-[9px] font-extrabold uppercase tracking-wider px-1.5 py-0.5 rounded border border-blue-500/20 bg-blue-500/10 text-blue-400">
                                                👤 {{ __('beach.flag_user_vote') }}
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-[11px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-full border {{ $dotColor }} text-white">
                                        @switch($displayFlag)
                                            @case('green') {{ __('common.flag_green') }} @break
                                            @case('yellow') {{ __('common.flag_yellow') }} @break
                                            @case('red') {{ __('common.flag_red') }} @break
                                            @case('blue_or_neutral') {{ __('common.flag_blue_or_neutral') }} @break
                                            @default {{ __('common.flag_none') }}
                                        @endswitch
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($totalSnapshots > 6)
                        <button @click="expanded = !expanded" class="w-full flex items-center justify-center gap-1.5 text-xs font-semibold text-blue-400 hover:text-blue-300 py-2 rounded-xl hover:bg-blue-500/5 transition-all active:scale-95 md:hidden">
                            <span x-text="expanded ? '{{ __('beach.history_show_less') }}' : '{{ __('beach.history_show_more', ['count' => $totalSnapshots - 6]) }}'"></span>
                            <svg class="w-3.5 h-3.5 transition-transform" :class="expanded && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    @endif
                </div>
            @endif
        </div>

            <!-- Right Column: Details & Forecast (Span 7) -->
            <div class="lg:col-span-7 space-y-6">
            <!-- Premium Metrics Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4" data-animate-stagger="0.06">
                <!-- Wave Height -->
                <div class="glass-card p-4 rounded-2xl text-center flex flex-col justify-between h-32 border border-theme-subtle/50 relative overflow-hidden group hover:border-sky-500/30 transition-all hover:shadow-lg hover:shadow-sky-500/5 card-lift">
                    <div class="absolute inset-0 bg-gradient-to-br from-sky-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative space-y-1">
                        <svg class="w-5 h-5 text-sky-400 mx-auto mb-1 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2 12c4-4 8 4 12 0s8-4 12 0M2 17c4-4 8 4 12 0s8-4 12 0"/>
                        </svg>
                        <span class="text-xs text-slate-400 uppercase font-bold block">{{ __('common.weather_waves') }}</span>
                        <span class="text-lg font-extrabold text-theme block">{{ $ocean && $ocean->wave_height_max !== null ? $ocean->wave_height_max . 'm' : __('common.weather_not_available') }}</span>
                    </div>
                    @if($ocean && $ocean->wave_direction)
                        <span class="text-[11px] text-slate-500 block truncate font-medium">{{ __('beach.dir_label', ['value' => $ocean->wave_direction]) }}</span>
                    @endif
                </div>

                <!-- Wave Period -->
                <div class="glass-card p-4 rounded-2xl text-center flex flex-col justify-between h-32 border border-theme-subtle/50 relative overflow-hidden group hover:border-indigo-500/30 transition-all hover:shadow-lg hover:shadow-indigo-500/5 card-lift">
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative space-y-1">
                        <svg class="w-5 h-5 text-indigo-400 mx-auto mb-1 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-xs text-slate-400 uppercase font-bold block">{{ __('beach.weather_wave_period') }}</span>
                        <span class="text-lg font-extrabold text-theme block">{{ $ocean && $ocean->wave_period_max !== null ? $ocean->wave_period_max . 's' : __('common.weather_not_available') }}</span>
                    </div>
                    @if($ocean && $ocean->wave_period_min !== null)
                        <span class="text-[11px] text-slate-500 block truncate font-medium">{{ __('beach.min_label', ['value' => $ocean->wave_period_min . 's']) }}</span>
                    @endif
                </div>

                <!-- Water Temp -->
                <div class="glass-card p-4 rounded-2xl text-center flex flex-col justify-between h-32 border border-theme-subtle/50 relative overflow-hidden group hover:border-blue-500/30 transition-all hover:shadow-lg hover:shadow-blue-500/5 card-lift">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative space-y-1">
                        <svg class="w-5 h-5 text-blue-400 mx-auto mb-1 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 2.69l5.66 5.66a8 8 0 11-11.31 0z"/>
                        </svg>
                        <span class="text-xs text-slate-400 uppercase font-bold block">{{ __('common.weather_water') }}</span>
                        <span class="text-lg font-extrabold text-theme block">{{ $ocean && $ocean->water_temp !== null ? $ocean->water_temp . '°C' : __('common.weather_not_available') }}</span>
                    </div>
                    <span class="text-[11px] text-slate-500 block truncate font-medium">{{ __('beach.sst_avg') }}</span>
                </div>

                <!-- Wind -->
                <div class="glass-card p-4 rounded-2xl text-center flex flex-col justify-between h-32 border border-theme-subtle/50 relative overflow-hidden group hover:border-teal-500/30 transition-all hover:shadow-lg hover:shadow-teal-500/5 card-lift">
                    <div class="absolute inset-0 bg-gradient-to-br from-teal-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative space-y-1">
                        <svg class="w-5 h-5 text-teal-400 mx-auto mb-1 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7H4M16 11H8M18 15H6M19 19H9"/>
                        </svg>
                        <span class="text-xs text-slate-400 uppercase font-bold block">{{ __('common.weather_wind') }}</span>
                        <span class="text-lg font-extrabold text-theme block">{{ $weather && $weather->wind_speed !== null ? (int)round($weather->wind_speed * 1.852) . ' km/h' : __('common.weather_not_available') }}</span>
                    </div>
                    @if($weather && $weather->wind_direction)
                        <span class="text-[11px] text-slate-500 block truncate font-medium">{{ __('beach.dir_label', ['value' => $weather->wind_direction]) }}</span>
                    @endif
                </div>

                <!-- Air Temp -->
                <div class="glass-card p-4 rounded-2xl text-center flex flex-col justify-between h-32 border border-theme-subtle/50 relative overflow-hidden group hover:border-amber-500/30 transition-all hover:shadow-lg hover:shadow-amber-500/5 card-lift">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative space-y-1">
                        @if($weather && $weather->weather_code !== null)
                            @php $wCode = $weather->weather_code; @endphp
                            @if($wCode === 0)
                                <svg class="w-6 h-6 text-amber-400 mx-auto mb-1" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="5"/><g stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="1" x2="12" y2="4"/><line x1="12" y1="20" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="6.34" y2="6.34"/><line x1="17.66" y1="17.66" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="4" y2="12"/><line x1="20" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="6.34" y2="17.66"/><line x1="17.66" y1="6.34" x2="19.78" y2="4.22"/></g></svg>
                            @elseif($wCode <= 3)
                                <svg class="w-6 h-6 mx-auto mb-1" viewBox="0 0 24 24"><circle cx="10" cy="8" r="3.5" fill="currentColor" class="text-amber-400"/><g stroke="currentColor" stroke-width="1.5" stroke-linecap="round" class="text-amber-300"><line x1="10" y1="1" x2="10" y2="3"/><line x1="4.5" y1="3.5" x2="5.9" y2="4.9"/><line x1="2.5" y1="9" x2="4.5" y2="9"/><line x1="15.5" y1="3.5" x2="14.1" y2="4.9"/></g><path d="M7 17a4.5 4.5 0 014.5-4.5h3a3.5 3.5 0 010 7H9a3 3 0 010-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-slate-400"/></svg>
                            @elseif($wCode <= 49)
                                <svg class="w-6 h-6 text-slate-400 mx-auto mb-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="10" x2="21" y2="10"/><line x1="5" y1="14" x2="19" y2="14"/><line x1="7" y1="18" x2="17" y2="18"/><line x1="4" y1="6" x2="20" y2="6" opacity=".4"/></svg>
                            @elseif($wCode <= 59)
                                <svg class="w-6 h-6 mx-auto mb-1" viewBox="0 0 24 24"><path d="M6 17a4.5 4.5 0 014.5-4.5h1a3.5 3.5 0 01.5 6.95" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-slate-400"/><path d="M9 12.5A5 5 0 0119 12a4 4 0 01-1 7.9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-slate-300"/><g stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-blue-400"><line x1="9" y1="19" x2="8" y2="21"/><line x1="14" y1="19" x2="13" y2="21"/></g></svg>
                            @elseif($wCode <= 69)
                                <svg class="w-6 h-6 mx-auto mb-1" viewBox="0 0 24 24"><path d="M6 17a4.5 4.5 0 014.5-4.5h1a3.5 3.5 0 01.5 6.95" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-slate-400"/><path d="M9 12.5A5 5 0 0119 12a4 4 0 01-1 7.9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-slate-300"/><g stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-blue-400"><line x1="8" y1="19" x2="7" y2="22"/><line x1="12" y1="19" x2="11" y2="22"/><line x1="16" y1="19" x2="15" y2="22"/></g></svg>
                            @elseif($wCode <= 79)
                                <svg class="w-6 h-6 mx-auto mb-1" viewBox="0 0 24 24"><path d="M6 17a4.5 4.5 0 014.5-4.5h1a3.5 3.5 0 01.5 6.95" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-slate-400"/><path d="M9 12.5A5 5 0 0119 12a4 4 0 01-1 7.9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-slate-300"/><g stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-cyan-300"><line x1="9" y1="19" x2="9" y2="21"/><line x1="13" y1="18" x2="13" y2="20"/><line x1="17" y1="19" x2="17" y2="21"/></g></svg>
                            @else
                                <svg class="w-6 h-6 mx-auto mb-1" viewBox="0 0 24 24"><path d="M6 17a4.5 4.5 0 014.5-4.5h1a3.5 3.5 0 01.5 6.95" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-slate-400"/><path d="M9 12.5A5 5 0 0119 12a4 4 0 01-1 7.9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-slate-300"/><path d="M13 15l-2 5m2-5l2 5m-2-5l2.5 3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-400"/></svg>
                            @endif
                        @else
                            <svg class="w-6 h-6 text-amber-400 mx-auto mb-1" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="5"/><g stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="1" x2="12" y2="4"/><line x1="12" y1="20" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="6.34" y2="6.34"/><line x1="17.66" y1="17.66" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="4" y2="12"/><line x1="20" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="6.34" y2="17.66"/><line x1="17.66" y1="6.34" x2="19.78" y2="4.22"/></g></svg>
                        @endif
                        <span class="text-xs text-slate-400 uppercase font-bold block">{{ __('common.weather_air') }}</span>
                        <span class="text-lg font-extrabold text-theme block">{{ $weather && $weather->temp !== null ? $weather->temp . '°C' : __('common.weather_not_available') }}</span>
                    </div>
                    @if($weather && $weather->precipitation !== null)
                        <span class="text-[11px] text-slate-500 block truncate font-medium">{{ __('beach.precip_label', ['value' => $weather->precipitation . 'mm']) }}</span>
                    @else
                        <span class="text-[11px] text-slate-500 block truncate font-medium">{{ __('beach.no_rain') }}</span>
                    @endif
                </div>

                <!-- Water Quality -->
                <div class="glass-card p-4 rounded-2xl text-center flex flex-col justify-between h-32 border border-theme-subtle/50 relative overflow-hidden group hover:border-emerald-500/30 transition-all hover:shadow-lg hover:shadow-emerald-500/5 card-lift">
                    @php
                        $qualityVal = $quality && $quality->quality_class && $quality->quality_class !== __('beach.quality_unknown') ? $quality->quality_class : __('beach.quality_unavailable');
                        $qualityColor = match($qualityVal) {
                            'Excellent' => 'text-emerald-400',
                            'Good' => 'text-teal-400',
                            'Sufficient' => 'text-amber-400',
                            'Poor' => 'text-rose-500',
                            default => 'text-slate-400'
                        };
                        $qualityText = match($qualityVal) {
                            'Excellent' => __('beach.quality_excellent'),
                            'Good' => __('beach.quality_good'),
                            'Sufficient' => __('beach.quality_sufficient'),
                            'Poor' => __('beach.quality_poor'),
                            default => __('beach.quality_unavailable')
                        };
                        $qualityDays = $quality && $quality->sampled_at ? now()->startOfDay()->diffInDays($quality->sampled_at) : null;
                        $qualityStale = $qualityDays !== null && $qualityDays > 14;
                    @endphp
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative space-y-1">
                        <svg class="w-5 h-5 text-emerald-400 mx-auto mb-1 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span class="text-xs text-slate-400 uppercase font-bold block">{{ __('common.weather_water') }}</span>
                        <span class="text-lg font-extrabold block {{ $qualityColor }}">{{ $qualityText }}</span>
                    </div>
                    <span class="text-[11px] {{ $qualityStale ? 'text-amber-400 font-bold' : 'text-slate-500' }} block truncate leading-tight">
                        {{ $quality && $quality->sampled_at ? __('beach.quality_analysis', ['date' => $quality->sampled_at->timezone($beach->timezone)->format('d/m/y')]) : __('beach.quality_analysis_none') }}
                        @if($qualityStale) · <span class="font-bold">{{ __('beach.quality_days', ['days' => $qualityDays]) }}</span> @endif
                    </span>
                    @if($quality && $quality->source)
                        <span class="text-[10px] text-slate-600 block leading-tight mt-0.5">
                            {{ __('beach.quality_source', ['source' => __('beach.quality_source_'.$quality->source)]) }}
                        </span>
                    @endif
                </div>
            </div>

            <x-ads.slot slot="beach_detail_inline" />

            <!-- Weather Forecast — Hourly + Daily -->
            @if(!empty($hourlyForecast) || !empty($dailyForecast))
            @php
                $now = \Carbon\Carbon::now($beach->timezone ?? config('app.timezone'));
                $currentHour = (int) $now->format('G');
                // Filter hourly to show only from now onwards (skip past hours)
                $upcomingHourly = collect($hourlyForecast)->filter(function ($h) use ($now) {
                    return \Carbon\Carbon::parse($h['time'])->greaterThanOrEqualTo($now->copy()->subHour());
                })->take(18)->values()->all();
                // Daily: skip today, take next 4 days
                $upcomingDaily = collect($dailyForecast)->filter(function ($d) {
                    return !\Carbon\Carbon::parse($d['date'])->isToday();
                })->take(4)->values()->all();
            @endphp
            <div x-data="{ forecastTab: 'hourly' }" class="glass-card rounded-3xl border border-theme-subtle/40 shadow-xl animate-fade-in-up overflow-x-hidden" data-animate>
                <!-- Header + Tabs -->
                <div class="flex items-center justify-between p-4 sm:p-5 border-b border-theme-subtle">
                    <h3 class="text-sm font-bold text-theme flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-400" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="5"/></svg>
                        {{ __('beach.weather_forecast_title') }}
                    </h3>
                    <div class="flex bg-theme-subtle/30 rounded-xl p-0.5">
                        <button @click="forecastTab = 'hourly'" :class="forecastTab === 'hourly' ? 'bg-theme-elevated text-theme font-bold shadow-sm' : 'text-theme-muted hover:text-theme-secondary'" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold uppercase tracking-wider transition-all">{{ __('beach.forecast_hourly') }}</button>
                        <button @click="forecastTab = 'daily'" :class="forecastTab === 'daily' ? 'bg-theme-elevated text-theme font-bold shadow-sm' : 'text-theme-muted hover:text-theme-secondary'" class="px-3 py-1.5 rounded-lg text-[11px] font-semibold uppercase tracking-wider transition-all">{{ __('beach.forecast_daily') }}</button>
                    </div>
                </div>

                <!-- Hourly Forecast Tab -->
                @if(!empty($upcomingHourly))
                <div x-show="forecastTab === 'hourly'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="overflow-x-auto scrollbar-none forecast-hourly-scroll">
                        <div class="flex min-w-max">
                            @foreach($upcomingHourly as $i => $hour)
                                @php
                                    $hTime = \Carbon\Carbon::parse($hour['time']);
                                    $isNow = $now->diffInMinutes($hTime) < 30;
                                    $code = $hour['weather_code'];
                                    $isDay = $hour['is_day'];
                                @endphp
                                <div class="flex flex-col items-center py-4 px-4 {{ $isNow ? 'bg-gradient-to-b from-amber-500/8 to-transparent min-w-[72px]' : 'min-w-[64px]' }} {{ $i === 0 ? 'pl-5' : '' }} border-r border-theme-subtle/20 last:border-r-0">
                                    @if($i === 0)
                                        <span class="text-[10px] font-bold text-amber-400 uppercase tracking-wider">{{ __('beach.forecast_now') }}</span>
                                    @else
                                        <span class="text-[11px] font-bold text-theme-secondary">{{ $hour['hour'] }}</span>
                                    @endif
                                    <div class="my-2">
                                        @if($code === 0)
                                            @if($isDay)
                                                <svg class="w-6 h-6 text-amber-400" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="5"/><g stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="1" x2="12" y2="4"/><line x1="12" y1="20" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="6.34" y2="6.34"/><line x1="17.66" y1="17.66" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="4" y2="12"/><line x1="20" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="6.34" y2="17.66"/><line x1="17.66" y1="6.34" x2="19.78" y2="4.22"/></g></svg>
                                            @else
                                                <svg class="w-6 h-6 text-indigo-300" viewBox="0 0 24 24" fill="currentColor"><path d="M20 12a8 8 0 01-8-8c0 1 1 3 1 3s-3 1-3 5a8 8 0 0010 0z"/></svg>
                                            @endif
                                        @elseif($code <= 3)
                                            @if($isDay)
                                                <svg class="w-6 h-6" viewBox="0 0 24 24"><circle cx="10" cy="8" r="3.5" fill="currentColor" class="text-amber-400"/><g stroke="currentColor" stroke-width="1.5" stroke-linecap="round" class="text-amber-300"><line x1="10" y1="1" x2="10" y2="3"/><line x1="4.5" y1="3.5" x2="5.9" y2="4.9"/><line x1="2.5" y1="9" x2="4.5" y2="9"/><line x1="15.5" y1="3.5" x2="14.1" y2="4.9"/></g><path d="M7 17a4.5 4.5 0 014.5-4.5h3a3.5 3.5 0 010 7H9a3 3 0 010-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-slate-400"/></svg>
                                            @else
                                                <svg class="w-6 h-6" viewBox="0 0 24 24"><path d="M20 12a8 8 0 01-8-8c0 1 1 3 1 3s-3 1-3 5a8 8 0 0010 0z" fill="currentColor" class="text-indigo-300"/><path d="M7 17a4.5 4.5 0 014.5-4.5h3a3.5 3.5 0 010 7H9a3 3 0 010-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-slate-400"/></svg>
                                            @endif
                                        @elseif($code <= 49)
                                            <svg class="w-6 h-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="10" x2="21" y2="10"/><line x1="5" y1="14" x2="19" y2="14"/><line x1="7" y1="18" x2="17" y2="18"/><line x1="4" y1="6" x2="20" y2="6" opacity=".4"/></svg>
                                        @elseif($code <= 59)
                                            <svg class="w-6 h-6" viewBox="0 0 24 24"><path d="M6 17a4.5 4.5 0 014.5-4.5h1a3.5 3.5 0 01.5 6.95" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-slate-400"/><path d="M9 12.5A5 5 0 0119 12a4 4 0 01-1 7.9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-slate-300"/><g stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-blue-400"><line x1="9" y1="19" x2="8" y2="21"/><line x1="14" y1="19" x2="13" y2="21"/></g></svg>
                                        @elseif($code <= 69)
                                            <svg class="w-6 h-6" viewBox="0 0 24 24"><path d="M6 17a4.5 4.5 0 014.5-4.5h1a3.5 3.5 0 01.5 6.95" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-slate-400"/><path d="M9 12.5A5 5 0 0119 12a4 4 0 01-1 7.9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-slate-300"/><g stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-blue-400"><line x1="8" y1="19" x2="7" y2="22"/><line x1="12" y1="19" x2="11" y2="22"/><line x1="16" y1="19" x2="15" y2="22"/></g></svg>
                                        @elseif($code <= 79)
                                            <svg class="w-6 h-6" viewBox="0 0 24 24"><path d="M6 17a4.5 4.5 0 014.5-4.5h1a3.5 3.5 0 01.5 6.95" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-slate-400"/><path d="M9 12.5A5 5 0 0119 12a4 4 0 01-1 7.9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-slate-300"/><g stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-cyan-300"><line x1="9" y1="19" x2="9" y2="21"/><line x1="13" y1="18" x2="13" y2="20"/><line x1="17" y1="19" x2="17" y2="21"/></g></svg>
                                        @else
                                            <svg class="w-6 h-6" viewBox="0 0 24 24"><path d="M6 17a4.5 4.5 0 014.5-4.5h1a3.5 3.5 0 01.5 6.95" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-slate-400"/><path d="M9 12.5A5 5 0 0119 12a4 4 0 01-1 7.9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-slate-300"/><path d="M13 15l-2 5m2-5l2 5m-2-5l2.5 3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-400"/></svg>
                                        @endif
                                    </div>
                                    <span class="text-sm font-extrabold text-theme">{{ round($hour['temp']) }}°</span>
                                    <span class="text-[8px] text-theme-muted leading-tight text-center max-w-[60px]">{{ __('beach.weather_' . $hour['condition']) }}</span>
                                    @if($hour['precipitation_probability'] > 0)
                                        <div class="flex items-center gap-0.5 mt-1">
                                            <svg class="w-3 h-3 text-blue-400" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2c-4 6-7 9-7 13a7 7 0 0014 0c0-4-3-7-7-13z"/></svg>
                                            <span class="text-[9px] text-blue-400 font-bold">{{ $hour['precipitation_probability'] }}%</span>
                                        </div>
                                    @else
                                        <div class="h-[14px]"></div>
                                    @endif
                                    <span class="text-[9px] text-theme-muted mt-0.5">{{ round($hour['wind_speed']) }} km/h</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Daily Forecast Tab -->
                @if(!empty($upcomingDaily))
                <div x-show="forecastTab === 'daily'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="p-4 sm:p-5 space-y-3">
                        @foreach($upcomingDaily as $day)
                            @php
                                $date = \Carbon\Carbon::parse($day['date']);
                                $isTomorrow = $date->isTomorrow();
                                $dayName = $isTomorrow ? __('common.tomorrow') : $date->translatedFormat('l');
                                $dateStr = $date->translatedFormat('d/M');
                                $code = $day['weather_code'];
                                $rainChance = $day['precipitation_probability'];
                            @endphp
                            <div class="flex items-center gap-3 p-3 rounded-2xl bg-theme-subtle/20 border border-theme-subtle/30">
                                <!-- Day + Date -->
                                <div class="w-20 shrink-0">
                                    <span class="text-xs font-bold text-theme block">{{ $dayName }}</span>
                                    <span class="text-[10px] text-theme-muted capitalize">{{ $dateStr }}</span>
                                </div>

                                <!-- Weather Icon + Condition -->
                                <div class="shrink-0 flex flex-col items-center gap-0.5">
                                    @if($code === 0)
                                        <svg class="w-7 h-7 text-amber-400" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="5"/><g stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="1" x2="12" y2="4"/><line x1="12" y1="20" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="6.34" y2="6.34"/><line x1="17.66" y1="17.66" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="4" y2="12"/><line x1="20" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="6.34" y2="17.66"/><line x1="17.66" y1="6.34" x2="19.78" y2="4.22"/></g></svg>
                                    @elseif($code <= 3)
                                        <svg class="w-7 h-7" viewBox="0 0 24 24"><circle cx="10" cy="8" r="3.5" fill="currentColor" class="text-amber-400"/><g stroke="currentColor" stroke-width="1.5" stroke-linecap="round" class="text-amber-300"><line x1="10" y1="1" x2="10" y2="3"/><line x1="4.5" y1="3.5" x2="5.9" y2="4.9"/><line x1="2.5" y1="9" x2="4.5" y2="9"/><line x1="15.5" y1="3.5" x2="14.1" y2="4.9"/></g><path d="M7 17a4.5 4.5 0 014.5-4.5h3a3.5 3.5 0 010 7H9a3 3 0 010-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-slate-400"/></svg>
                                    @elseif($code <= 49)
                                        <svg class="w-7 h-7 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="10" x2="21" y2="10"/><line x1="5" y1="14" x2="19" y2="14"/><line x1="7" y1="18" x2="17" y2="18"/><line x1="4" y1="6" x2="20" y2="6" opacity=".4"/></svg>
                                    @elseif($code <= 59)
                                        <svg class="w-7 h-7" viewBox="0 0 24 24"><path d="M6 17a4.5 4.5 0 014.5-4.5h1a3.5 3.5 0 01.5 6.95" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-slate-400"/><path d="M9 12.5A5 5 0 0119 12a4 4 0 01-1 7.9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-slate-300"/><g stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-blue-400"><line x1="8" y1="19" x2="7" y2="22"/><line x1="12" y1="19" x2="11" y2="22"/><line x1="16" y1="19" x2="15" y2="22"/></g></svg>
                                    @elseif($code <= 69)
                                        <svg class="w-7 h-7" viewBox="0 0 24 24"><path d="M6 17a4.5 4.5 0 014.5-4.5h1a3.5 3.5 0 01.5 6.95" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-slate-400"/><path d="M9 12.5A5 5 0 0119 12a4 4 0 01-1 7.9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-slate-300"/><g stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-blue-400"><line x1="8" y1="19" x2="7" y2="22"/><line x1="12" y1="19" x2="11" y2="22"/><line x1="16" y1="19" x2="15" y2="22"/></g></svg>
                                    @elseif($code <= 79)
                                        <svg class="w-7 h-7" viewBox="0 0 24 24"><path d="M6 17a4.5 4.5 0 014.5-4.5h1a3.5 3.5 0 01.5 6.95" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-slate-400"/><path d="M9 12.5A5 5 0 0119 12a4 4 0 01-1 7.9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-slate-300"/><g stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-cyan-300"><line x1="9" y1="19" x2="9" y2="21"/><line x1="13" y1="18" x2="13" y2="20"/><line x1="17" y1="19" x2="17" y2="21"/></g></svg>
                                    @else
                                        <svg class="w-7 h-7" viewBox="0 0 24 24"><path d="M6 17a4.5 4.5 0 014.5-4.5h1a3.5 3.5 0 01.5 6.95" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-slate-400"/><path d="M9 12.5A5 5 0 0119 12a4 4 0 01-1 7.9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-slate-300"/><path d="M13 15l-2 5m2-5l2 5m-2-5l2.5 3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-400"/></svg>
                                    @endif
                                    <span class="text-[8px] text-theme-muted leading-none">{{ __('beach.weather_' . $day['condition']) }}</span>
                                </div>

                                <!-- Rain probability bar -->
                                <div class="flex-1 min-w-0">
                                    @if($rainChance > 0)
                                        <div class="flex items-center gap-1.5 mb-1">
                                            <svg class="w-3 h-3 text-blue-400 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2c-4 6-7 9-7 13a7 7 0 0014 0c0-4-3-7-7-13z"/></svg>
                                            <div class="flex-1 h-1.5 bg-theme-subtle/40 rounded-full overflow-hidden">
                                                <div class="h-full bg-gradient-to-r from-blue-400 to-blue-500 rounded-full transition-all" style="width: {{ $rainChance }}%"></div>
                                            </div>
                                            <span class="text-[10px] text-blue-400 font-bold tabular-nums">{{ $rainChance }}%</span>
                                        </div>
                                    @endif
                                    <div class="flex items-center gap-1 text-[10px] text-theme-muted">
                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 2v10m0 0l3-3m-3 3l-3-3"/></svg>
                                        <span>{{ round($day['wind_speed']) }} km/h {{ $day['wind_direction'] }}</span>
                                    </div>
                                </div>

                                <!-- Temps -->
                                <div class="flex items-baseline gap-1 shrink-0">
                                    <span class="text-base font-extrabold text-theme">{{ round($day['temp_max']) }}°</span>
                                    <span class="text-xs text-theme-muted">{{ round($day['temp_min']) }}°</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endif

            <!-- Custom Tabbed Tides & Moon Panel -->
            <div x-data="{ activeTideTab: 'tides' }" class="glass-card rounded-3xl overflow-hidden border border-theme-subtle/40 shadow-xl animate-fade-in-up" data-animate>
                <!-- Tab Headers -->
                <div class="flex border-b border-theme-subtle bg-slate-950/30 p-2 gap-2">
                    <button @click="activeTideTab = 'tides'" role="tab" :aria-selected="activeTideTab === 'tides'"
                            :class="activeTideTab === 'tides' ? 'bg-blue-600/10 border-blue-500/20 text-blue-400 font-bold shadow-sm' : 'text-slate-400 hover:text-slate-200'" 
                            class="flex-1 py-2.5 rounded-xl border border-transparent text-sm transition-all flex items-center justify-center gap-2 active:scale-95">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 15c2-3 4-5 6-5s4 3 6 3 4-3 6-3"/><path d="M3 19c2-3 4-5 6-5s4 3 6 3 4-3 6-3" opacity=".4"/></svg>
                        {{ __('beach.tide_title') }}
                    </button>
                    <button @click="activeTideTab = 'moon'" role="tab" :aria-selected="activeTideTab === 'moon'"
                            :class="activeTideTab === 'moon' ? 'bg-indigo-600/10 border-indigo-500/20 text-indigo-400 font-bold shadow-sm' : 'text-slate-400 hover:text-slate-200'" 
                            class="flex-1 py-2.5 rounded-xl border border-transparent text-sm transition-all flex items-center justify-center gap-2 active:scale-95">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M20 12a8 8 0 01-8-8c0 1 1 3 1 3s-3 1-3 5a8 8 0 0010 0z"/></svg>
                        {{ __('beach.moon_title') }}
                    </button>
                </div>
                
                <!-- Tab Contents -->
                <div class="p-6">
                    <!-- Tab 1: Tides -->
                    <div x-show="activeTideTab === 'tides'" role="tabpanel" class="space-y-6" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                        <div class="flex items-center justify-between">
                            <div class="space-y-0.5">
                                <span class="text-xs text-slate-400 uppercase tracking-widest font-bold">{{ __('beach.tide_state') }}</span>
                                @if($nextTide)
                                    <div class="flex items-center gap-2 text-sm {{ $tideDirection === 'up' ? 'text-sky-400 font-bold' : 'text-amber-400 font-bold' }}">
                                        <span>{{ $tideDirection === 'up' ? __('beach.tide_rising') : __('beach.tide_falling') }}</span>
                                        <span class="text-slate-500 font-normal">· {{ __('beach.tide_next', ['tide' => $nextTide->tide_type === 'high' ? __('beach.tide_high_name') : __('beach.tide_low_name'), 'time' => $nextTide->tide_time->timezone($beach->timezone)->format('H:i')]) }}</span>
                                    </div>
                                @endif
                            </div>
                            <span class="text-xs text-slate-500 font-semibold bg-slate-900 border border-theme-subtle/80 px-2.5 py-1 rounded-full">{{ __('beach.tide_source') }}</span>
                        </div>

                        @if(!empty($tideCurve))
                            <div class="relative h-20 sm:h-24 bg-slate-950/20 rounded-2xl border border-theme-subtle/30 p-2 overflow-hidden">
                                <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="w-full h-full">
                                    <defs>
                                        <linearGradient id="tideGradient" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0%" stop-color="#38bdf8" stop-opacity="0.2"/>
                                            <stop offset="100%" stop-color="#38bdf8" stop-opacity="0.0"/>
                                        </linearGradient>
                                        <filter id="tideGlow" x="-20%" y="-20%" width="140%" height="140%">
                                            <feGaussianBlur stdDeviation="1.5" result="blur" />
                                            <feMerge>
                                                <feMergeNode in="blur" />
                                                <feMergeNode in="SourceGraphic" />
                                            </feMerge>
                                        </filter>
                                    </defs>
                                    <polygon fill="url(#tideGradient)" points="0,95 {{ trim($tideCurvePoints) }} 100,95"></polygon>
                                    <polyline fill="none" stroke="#0284c7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" filter="url(#tideGlow)" points="{{ trim($tideCurvePoints) }}"></polyline>
                                    
                                    @php
                                        $nowPct = 0;
                                        $nowY = 50;
                                        $nowStr = now()->timezone($beach->timezone)->format('H:i');
                                        foreach ($tideCurve as $k => $pt) {
                                            if ($pt['time'] >= $nowStr) {
                                                $nowPct = $k / max(count($tideCurve) - 1, 1) * 100;
                                                $pct = $pt['pct'];
                                                $nowY = 95 - $pct * 85;
                                                break;
                                            }
                                        }
                                    @endphp
                                    <line x1="{{ $nowPct }}" y1="5" x2="{{ $nowPct }}" y2="95" stroke="rgba(245, 158, 11, 0.3)" stroke-width="1.5" stroke-dasharray="3,3"/>
                                    <circle cx="{{ $nowPct }}" cy="{{ $nowY }}" r="5" fill="#f59e0b" fill-opacity="0.4" class="animate-ping"/>
                                    <circle cx="{{ $nowPct }}" cy="{{ $nowY }}" r="3" fill="#f59e0b" stroke="#0f172a" stroke-width="1"/>
                                </svg>
                                <span class="absolute text-[11px] font-bold text-amber-500 bg-slate-950/80 px-1.5 py-0.5 rounded border border-amber-500/20" style="left: calc({{ $nowPct }}% - 22px); top: 6px;">{{ __('beach.tide_now') }}</span>
                            </div>
                        @endif

                        @if($tides->isNotEmpty())
                            <div class="space-y-5 pt-2">
                                <div>
                                    <span class="text-[11px] text-slate-400 uppercase tracking-widest font-extrabold block mb-2.5">{{ __('beach.tide_today_label') }}</span>
                                    <div class="grid grid-cols-2 gap-3">
                                        @foreach($tidesToday as $tide)
                                            @php
                                                $isPast = $tide->tide_time->isPast();
                                                $isNext = $nextTide && $nextTide->tide_time->eq($tide->tide_time);
                                                $typeColor = $tide->tide_type === 'high' ? 'sky' : 'amber';
                                                $borderClass = $isNext 
                                                    ? 'border-blue-500 bg-blue-500/5 shadow-lg shadow-blue-500/5' 
                                                    : 'border-theme-subtle/50 bg-slate-900/30';
                                            @endphp
                                            <div class="glass-card p-3 rounded-2xl border flex items-center justify-between gap-3 relative transition-all {{ $borderClass }} {{ $isPast ? 'opacity-40' : 'hover:border-'.$typeColor.'-500/30 hover:bg-slate-900/50' }}">
                                                @if($isNext)
                                                    <div class="absolute -top-2 -right-2 bg-blue-500 text-slate-950 text-[9px] font-black uppercase px-2 py-0.5 rounded-full shadow-sm animate-pulse">
                                                        {{ __('beach.tide_next_label') }}
                                                    </div>
                                                @endif
                                                
                                                <div class="flex items-center gap-2.5 min-w-0">
                                                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 {{ $tide->tide_type === 'high' ? 'bg-sky-500/10 text-sky-400' : 'bg-amber-500/10 text-amber-400' }}">
                                                        <span>{{ $tide->tide_type === 'high' ? '▲' : '▼' }}</span>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <span class="text-[9px] text-slate-400 uppercase tracking-wider font-extrabold block">
                                                            {{ $tide->tide_type === 'high' ? __('beach.tide_high_name') : __('beach.tide_low_name') }}
                                                        </span>
                                                        <span class="text-sm font-black text-theme tabular-nums">
                                                            {{ $tide->tide_time->timezone($beach->timezone)->format('H:i') }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="text-right shrink-0">
                                                    <span class="text-xs font-extrabold text-theme tabular-nums">
                                                        {{ $tide->tide_height }}m
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                @if($tidesTomorrow->isNotEmpty())
                                    <div class="pt-1">
                                        <span class="text-[11px] text-slate-400 uppercase tracking-widest font-extrabold block mb-2.5">{{ __('beach.tide_tomorrow_label') }}</span>
                                        <div class="grid grid-cols-2 gap-3">
                                            @foreach($tidesTomorrow as $tide)
                                                @php
                                                    $typeColor = $tide->tide_type === 'high' ? 'sky' : 'amber';
                                                @endphp
                                                <div class="glass-card p-3 rounded-2xl border border-theme-subtle/50 bg-slate-900/30 flex items-center justify-between gap-3 relative transition-all hover:border-{{ $typeColor }}-500/30 hover:bg-slate-900/50">
                                                    <div class="flex items-center gap-2.5 min-w-0">
                                                        <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 {{ $tide->tide_type === 'high' ? 'bg-sky-500/10 text-sky-400' : 'bg-amber-500/10 text-amber-400' }}">
                                                            <span>{{ $tide->tide_type === 'high' ? '▲' : '▼' }}</span>
                                                        </div>
                                                        <div class="min-w-0">
                                                            <span class="text-[9px] text-slate-400 uppercase tracking-wider font-extrabold block">
                                                                {{ $tide->tide_type === 'high' ? __('beach.tide_high_name') : __('beach.tide_low_name') }}
                                                            </span>
                                                            <span class="text-sm font-black text-theme tabular-nums">
                                                                {{ $tide->tide_time->timezone($beach->timezone)->format('H:i') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="text-right shrink-0">
                                                        <span class="text-xs font-extrabold text-theme tabular-nums">
                                                            {{ $tide->tide_height }}m
                                                        </span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <p class="text-xs text-slate-500 text-center py-4">{{ __('beach.tide_none') }}</p>
                        @endif
                    </div>

                    <!-- Tab 2: Moon Cycle -->
                    <div x-show="activeTideTab === 'moon'" role="tabpanel" class="space-y-6" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                        <div class="flex flex-col items-center text-center space-y-4">
                            <div class="relative w-24 h-24 rounded-full bg-slate-950 flex items-center justify-center border border-indigo-500/25">
                                <div class="absolute inset-0 rounded-full blur-2xl opacity-25 bg-indigo-500"></div>
                                <span class="text-6xl relative z-10 select-none animate-float">{{ \App\Models\TideForecast::moonPhaseIcon($moonPhase) }}</span>
                            </div>

                            <div class="space-y-1">
                                <span class="text-xs text-slate-500 uppercase tracking-widest font-bold block">{{ __('beach.moon_current') }}</span>
                                <h4 class="text-2xl font-black text-theme tracking-tight">{{ \App\Models\TideForecast::moonPhaseName($moonPhase) }}</h4>
                                @php
                                    $moonIllumination = round((1 - cos(2 * M_PI * $moonPhase)) / 2 * 100);
                                @endphp
                                <p class="text-sm text-slate-400">{{ __('beach.moon_illumination', ['pct' => $moonIllumination]) }}</p>
                            </div>

                            <div class="w-full max-w-xs space-y-1 pt-2">
                                <div class="flex justify-between text-[11px] text-slate-500 font-bold uppercase">
                                    <span>{{ __('common.moon_new') }}</span>
                                    <span>{{ __('beach.moon_cycle', ['currentd' => round($moonPhase * 29.53, 1), 'totald' => '29.5']) }}</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-slate-900 border border-theme-subtle overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-indigo-500 via-purple-500 to-indigo-500 rounded-full transition-all duration-500" style="width: {{ $moonPhase * 100 }}%"></div>
                                </div>
                            </div>
                        </div>

                        @if(count($upcomingMoonPhases) > 0)
                            <div class="pt-4 border-t border-theme-subtle space-y-3">
                                <span class="text-xs text-slate-400 uppercase font-bold tracking-wider block">{{ __('beach.moon_upcoming_phases') }}</span>
                                <div class="grid grid-cols-4 gap-2 text-xs" data-animate-stagger="0.08">
                                    @foreach($upcomingMoonPhases as $item)
                                        <div class="p-2.5 rounded-2xl bg-theme-card border border-theme-subtle/80 text-center space-y-1 hover:border-indigo-500/25 transition-all hover:shadow-lg hover:shadow-indigo-500/5 card-lift">
                                            <span class="text-xl block select-none">{{ $item['icon'] }}</span>
                                            <span class="font-bold text-theme block leading-tight truncate">{{ $item['name'] }}</span>
                                            <span class="text-slate-400 block font-medium">{{ $item['date']->timezone($beach->timezone)->format('d/m') }}</span>
                                            <span class="text-[11px] text-slate-500 block">{{ __('beach.moon_in_days', ['days' => (int)round($item['days_until'])]) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Description & Services -->
            @php
                $translateFeature = function ($val) {
                    if (!$val) return '';
                    $key = 'common.feature_val_' . $val;
                    return Illuminate\Support\Facades\Lang::has($key) ? __($key) : $val;
                };
            @endphp
            <div class="glass-card overflow-hidden rounded-3xl border border-theme-subtle/40 animate-fade-in-up" data-animate>
                <div class="relative px-5 pt-5 pb-3">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500/60 via-sky-400/40 to-transparent"></div>
                    <div class="flex items-center gap-2.5">
                        <span class="flex items-center justify-center w-9 h-9 rounded-xl bg-blue-500/15 text-blue-400 text-base shrink-0 shadow-sm shadow-blue-500/5">ℹ️</span>
                        <div>
                            <h3 class="text-base font-extrabold text-theme tracking-tight">{{ __('common.about_title') }}</h3>
                            @if($beach->features && $beach->features->beach_type)
                                <span class="text-[11px] uppercase tracking-wider text-slate-500 font-semibold">{{ $translateFeature($beach->features->beach_type) }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="px-5 pb-5">
                    <div class="relative pl-4 border-l-2 border-blue-500/40 py-1 bg-blue-500/[0.02] rounded-r-xl pr-2">
                        <p class="text-[14px] text-slate-200 leading-relaxed font-medium">
                            {{ $beach->description ?: 'Praia oficial vigiada com excelente época balnear e águas de classificação periódica ótima.' }}
                        </p>
                    </div>

                    @if($beach->features && ($beach->features->coast_orientation || $beach->features->bottom_type || $beach->features->slope || $beach->features->exposure_direction))
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-5">
                            @if($beach->features->coast_orientation)
                                <div class="glass-card p-3 rounded-2xl border border-theme-subtle/50 bg-slate-900/30 text-center space-y-1 transition-all hover:border-blue-500/20 card-lift">
                                    <span class="text-lg block select-none">🧭</span>
                                    <span class="text-[10px] text-slate-400 uppercase tracking-widest font-extrabold block">{{ __('common.about_features_orientation_title') }}</span>
                                    <span class="text-xs font-black text-theme block truncate">{{ $translateFeature($beach->features->coast_orientation) }}</span>
                                </div>
                            @endif
                            @if($beach->features->bottom_type)
                                <div class="glass-card p-3 rounded-2xl border border-theme-subtle/50 bg-slate-900/30 text-center space-y-1 transition-all hover:border-blue-500/20 card-lift">
                                    <span class="text-lg block select-none">🏖️</span>
                                    <span class="text-[10px] text-slate-400 uppercase tracking-widest font-extrabold block">{{ __('common.about_features_bottom_title') }}</span>
                                    <span class="text-xs font-black text-theme block truncate">{{ $translateFeature($beach->features->bottom_type) }}</span>
                                </div>
                            @endif
                            @if($beach->features->slope)
                                <div class="glass-card p-3 rounded-2xl border border-theme-subtle/50 bg-slate-900/30 text-center space-y-1 transition-all hover:border-blue-500/20 card-lift">
                                    <span class="text-lg block select-none">📐</span>
                                    <span class="text-[10px] text-slate-400 uppercase tracking-widest font-extrabold block">{{ __('common.about_features_slope_title') }}</span>
                                    <span class="text-xs font-black text-theme block truncate">{{ $translateFeature($beach->features->slope) }}</span>
                                </div>
                            @endif
                            @if($beach->features->exposure_direction)
                                <div class="glass-card p-3 rounded-2xl border border-theme-subtle/50 bg-slate-900/30 text-center space-y-1 transition-all hover:border-blue-500/20 card-lift">
                                    <span class="text-lg block select-none">🌊</span>
                                    <span class="text-[10px] text-slate-400 uppercase tracking-widest font-extrabold block">{{ __('common.about_features_exposure_title') }}</span>
                                    <span class="text-xs font-black text-theme block truncate">{{ $translateFeature($beach->features->exposure_direction) }}</span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Services -->
                <div class="border-t border-white/[0.04] [data-theme=light]:border-black/[0.04]">
                    <div class="px-5 py-4">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ __('common.about_services') }}
                            </h4>
                            @if($beach->services)
                                @php
                                    $serviceFields = ['parking', 'bathrooms', 'showers', 'accessible', 'amphibious_chair', 'first_aid', 'lifeguard_post', 'bar', 'restaurant', 'surf_school', 'equipment_rental'];
                                    $activeServices = count(array_filter($serviceFields, fn($f) => $beach->services->$f));
                                @endphp
                                <span class="text-[11px] font-bold text-blue-400 bg-blue-500/10 px-2 py-0.5 rounded-full border border-blue-500/15">
                                    {{ $activeServices === 1 ? __('common.about_services_count_singular', ['count' => $activeServices]) : __('common.about_services_count', ['count' => $activeServices]) }}
                                </span>
                            @endif
                        </div>

                        @if($beach->services && $activeServices > 0)
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach($serviceFields as $field)
                                    @if($beach->services->$field)
                                        @php
                                            $label = __('common.service_' . $field);
                                            $parts = explode(' ', $label, 2);
                                            $emoji = $parts[0] ?? '📋';
                                            $text = $parts[1] ?? $label;
                                        @endphp
                                        <div class="glass-card p-3 rounded-2xl border border-theme-subtle/50 bg-slate-900/30 flex items-center gap-3 hover:border-blue-500/20 hover:bg-slate-900/40 transition-all card-lift">
                                            <div class="w-9 h-9 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0 shadow-sm border border-blue-500/10 text-lg">
                                                {{ $emoji }}
                                            </div>
                                            <span class="text-xs font-bold text-slate-200 truncate leading-tight">{{ $text }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="flex items-center gap-2 py-2">
                                <span class="text-lg">📋</span>
                                <p class="text-xs text-slate-500">{{ __('common.about_no_services') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <x-ads.slot slot="beach_detail_bottom" />

            {{-- Restaurants (hidden until nearby dining is fixed) --}}
        </div>
    </div>

    <!-- Leaflet single location map section -->
    <div class="glass-card p-4 rounded-3xl border border-theme-medium animate-fade-in-up" data-animate
         x-intersect="$el.querySelector('#beach-map').setAttribute('data-visible', '1')">
        <h3 class="text-sm font-bold text-theme uppercase tracking-wider mb-3"><span aria-hidden="true">📍</span> {{ __('beach.map_location') }}</h3>
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
                isAdmin: false,

                init() {
                    this.isAdmin = this.$el.dataset.isAdmin === '1';
                    this.$nextTick(() => this.loadMap());
                },

                loadMap() {
                    const el = document.getElementById('beach-map');
                    if (!el || typeof L === 'undefined') {
                        setTimeout(() => this.loadMap(), 100);
                        return;
                    }
                    if (!el.dataset.visible) {
                        setTimeout(() => this.loadMap(), 200);
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

                    setTimeout(() => {
                        el.querySelectorAll('.leaflet-control-zoom a').forEach(a => {
                            if (a.getAttribute('href') === '#') {
                                a.removeAttribute('href');
                                a.setAttribute('role', 'button');
                                a.setAttribute('tabindex', '0');
                            }
                        });
                    }, 200);

                    let tileCounter = 0;
                    const layer = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> &copy; <a href="https://carto.com/">CARTO</a>',
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
                    window.CheckPraiaPermissions.requestLocation('report_flag').then((position) => {
                        $wire.call('submitReport', flagColor, position.coords.latitude, position.coords.longitude, position.coords.accuracy);
                        this.locating = false;
                    }).catch((err) => {
                        if (this.isAdmin) {
                            $wire.call('submitReport', flagColor, 0, 0, null);
                            this.locating = false;
                            return;
                        }
                        const msgs = {
                            'GPS_NOT_SUPPORTED': '{{ __('common.gps_not_supported') }}',
                            'GPS_DENIED': '{{ __('common.gps_denied') }}',
                            'GPS_ERROR': '{{ __('common.gps_error') }}',
                        };
                        this.$dispatch('notify', { message: msgs[err.message] || '{{ __('common.gps_error') }}', type: 'error' });
                        this.locating = false;
                    });
                }
            }));
        </script>
    @endscript
</div>
