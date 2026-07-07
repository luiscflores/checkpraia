<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark" x-data="themeHandler()">
<head>
    <script>
        (function() {
            const saved = localStorage.getItem('checkpraia-theme');
            const theme = saved || 'dark';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="dark light">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#020617" media="(prefers-color-scheme: dark)">
    <meta name="theme-color" content="#dee4ec" media="(prefers-color-scheme: light)">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="CheckPraia">
    <link rel="apple-touch-icon" href="/icon-180.png">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="192x192" href="/icon-192.png">
    <meta name="mobile-web-app-capable" content="yes">
    <meta http-equiv="Accept-CH" content="DPR, Width, Viewport-Width">

    <title>@yield('title', __('common.site_name') . ' - ' . __('common.site_description'))</title>
    <meta name="description" content="@yield('meta_description', __('common.meta_description'))">

    <!-- Canonical URL -->
    <link rel="canonical" href="@yield('canonical', url()->current())">

    <!-- Hreflang / Alternate Language URLs (override per page for dynamic routes) -->
    @section('hreflang')
        @foreach(['pt', 'en', 'es', 'fr'] as $locale)
            <link rel="alternate" hreflang="{{ $locale }}" href="{{ url($locale === 'pt' ? '' : "/{$locale}") }}">
        @endforeach
    @show
    <link rel="alternate" hreflang="x-default" href="{{ url('/') }}">

    <!-- Open Graph -->
    <meta property="og:title" content="@yield('og_title', __('common.site_name'))">
    <meta property="og:description" content="@yield('og_description', __('common.meta_description'))">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    <meta property="og:image" content="@yield('og_image', asset('storage/logo.png'))">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="388">
    <meta property="og:site_name" content="{{ __('common.site_name') }}">
    <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', __('common.site_name'))">
    <meta name="twitter:description" content="@yield('og_description', __('common.meta_description'))">
    <meta name="twitter:image" content="@yield('og_image', asset('storage/logo.png'))">

    <!-- Fonts: Plus Jakarta Sans with display=swap to never block rendering -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet" media="print" onload="this.media='all'">

    <!-- Preconnect for map tiles (non-blocking) -->
    <link rel="preconnect" href="https://unpkg.com">
    <link rel="dns-prefetch" href="https://unpkg.com">
    <link rel="preconnect" href="https://server.arcgisonline.com">
    <link rel="dns-prefetch" href="https://tile.openstreetmap.org">
    <link rel="dns-prefetch" href="https://basemaps.cartocdn.com">

    <!-- Leaflet CSS (non-blocking: print media, then switch to all after load) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""></noscript>

    <!-- Structured Data (JSON-LD) -->
    @section('ld_json')
    <script type="application/ld+json">
    @php echo json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Organization',
                '@id' => url('/') . '#organization',
                'name' => 'CheckPraia',
                'url' => url('/'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('storage/logo.png'),
                    'width' => 1162,
                    'height' => 376,
                ],
                'description' => __('common.site_description'),
                'areaServed' => ['@type' => 'Country', 'name' => 'Portugal'],
                'foundingDate' => '2025',
            ],
            [
                '@type' => 'WebSite',
                '@id' => url('/') . '#website',
                'url' => url('/'),
                'name' => __('common.site_name'),
                'description' => __('common.site_description'),
                'publisher' => ['@id' => url('/') . '#organization'],
                'inLanguage' => ['pt', 'en', 'es', 'fr'],
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => [
                        '@type' => 'EntryPoint',
                        'urlTemplate' => url('/') . '/?q={search_term_string}',
                    ],
                    'query-input' => 'required name=search_term_string',
                ],
            ],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); @endphp
    </script>
    @show

    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-base-gradient);
            color: var(--text-primary);
            transition: background 0.3s ease, color 0.3s ease;
        }
        .glass-card {
            background: var(--bg-glass-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--bg-glass-border);
            box-shadow: var(--bg-glass-shadow);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .glass-input {
            background: var(--bg-input);
            border: 1px solid var(--border-strong);
            color: var(--text-primary);
            transition: all 0.2s ease-in-out;
        }
        .glass-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
        }
        .glass-input::placeholder {
            color: var(--text-muted);
        }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: var(--scrollbar-track); }
        ::-webkit-scrollbar-thumb { background: var(--scrollbar-thumb); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--scrollbar-thumb-hover); }

        .beach-popup .leaflet-popup-content-wrapper {
            background: transparent;
            box-shadow: none;
            border-radius: 16px;
            padding: 0;
            overflow: hidden;
        }
        .beach-popup .leaflet-popup-tip {
            background: var(--bg-elevated);
            box-shadow: none;
        }
        .beach-popup .leaflet-popup-content {
            margin: 0;
            min-width: 180px;
        }
        .beach-popup .leaflet-popup-close-button { display: none; }
        .beach-popup-inner {
            background: var(--popup-bg);
            border-radius: 16px;
            padding: 14px 16px;
            box-shadow: var(--popup-shadow);
            border: 1px solid var(--popup-border);
        }
        .beach-popup-name {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .beach-popup-location {
            font-size: 11px;
            color: var(--text-secondary);
            margin-top: 2px;
        }
        .beach-popup .beach-popup-btn {
            display: block;
            text-align: center;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #fff !important;
            padding: 7px 14px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 12px;
            margin-top: 10px;
            letter-spacing: 0.02em;
            transition: all 0.15s;
            border: 1px solid var(--popup-border);
        }
        .beach-popup .beach-popup-btn:active {
            transform: scale(0.97);
            background: #1d4ed8;
        }
        @media (max-width: 640px) {
            .beach-popup .leaflet-popup-content {
                min-width: 160px;
                max-width: 220px;
            }
        }
        .theme-toggle-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            border-radius: 10px;
            color: var(--text-secondary);
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }
        .theme-toggle-btn:hover {
            background: var(--bg-glass-bg);
            color: var(--text-primary);
        }
        [data-theme="light"] .theme-toggle-dark-icon { display: none; }
        [data-theme="dark"] .theme-toggle-light-icon { display: none; }
        [data-theme="light"] .theme-toggle-light-icon { display: block; }
        [data-theme="dark"] .theme-toggle-dark-icon { display: block; }

        [data-theme="light"] .text-slate-400 { color: #64748b; }
        [data-theme="light"] .text-slate-500 { color: #475569; }
        [data-theme="light"] .bg-white\/5,
        [data-theme="light"] .bg-white\/\[5\%\] { background: rgba(0, 0, 0, 0.04); }
        [data-theme="light"] .bg-slate-900\/60 { background: rgba(226, 232, 240, 0.8); }
        [data-theme="light"] .bg-slate-800\/80 { background: rgba(203, 213, 225, 0.8); }
        [data-theme="light"] .glass-card .text-slate-300,
        [data-theme="light"] .bg-theme-card .text-slate-300 { color: #475569; }
        [data-theme="light"] .glass-card .text-slate-200 { color: #334155; }
        [data-theme="light"] .bg-rose-950\/20 { background: rgba(254, 202, 202, 0.3); }
        [data-theme="light"] .text-rose-200 { color: #c53030; }
        [data-theme="light"] .text-red-300 { color: #c53030; }
        [data-theme="light"] .text-red-400 { color: #dc2626; }
        [data-theme="light"] .bg-red-950\/10 { background: rgba(254, 202, 202, 0.2); }
        [data-theme="light"] .bg-blue-950\/10 { background: rgba(219, 234, 254, 0.4); }
        [data-theme="light"] .text-blue-300 { color: #2563eb; }
    </style>

    @if(app()->environment('production'))
        @php $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true); @endphp
        @if(isset($manifest['resources/js/app.js']['file']))
            <link rel="modulepreload" href="{{ asset('build/' . $manifest['resources/js/app.js']['file']) }}">
        @endif
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    @if(config('ads.publisher_id'))
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-{{ config('ads.publisher_id') }}"
                crossorigin="anonymous"></script>
    @endif
</head>
<body class="antialiased min-h-screen flex flex-col selection:bg-blue-600 selection:text-white" style="background: var(--bg-base); color: var(--text-primary);">

    <!-- Skip to content -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[100] focus:px-4 focus:py-2 focus:bg-blue-600 focus:text-white focus:rounded-xl focus:font-bold focus:text-sm focus:outline-none">
        Saltar para o conteúdo
    </a>

    <div class="w-full flex-1 flex flex-col relative">

        <!-- Header -->
        <header class="sticky top-0 z-50 bg-theme-header backdrop-blur-md border-b border-theme-subtle transition-all duration-300 pt-safe" role="banner">
            <div class="w-full max-w-7xl mx-auto px-4 sm:px-5 md:px-6 py-3 flex items-center justify-between pl-safe pr-safe">
                <a href="{{ route('home') }}" class="flex items-center gap-2 group shrink-0" aria-label="{{ __('common.nav_home') }}">
                    <img src="{{ asset('storage/logo.png') }}" alt="{{ __('common.site_name') }}" width="132" height="48" class="h-11 sm:h-12 w-auto transition-transform duration-300 group-hover:scale-105" fetchpriority="high">
                </a>

                <!-- Desktop Navigation Menu -->
                <nav class="hidden md:flex items-center gap-4 lg:gap-6" aria-label="{{ __('common.nav_map') }}">
                    <a href="{{ route('home') }}" class="relative text-xs font-bold uppercase tracking-wider transition-all duration-200 {{ request()->routeIs('home*') ? 'text-blue-400 font-extrabold' : 'text-theme-secondary hover:text-theme hover:scale-105' }}" {{ request()->routeIs('home*') ? 'aria-current="page"' : '' }}>
                        {{ __('common.nav_map') }}
                        @if(request()->routeIs('home*'))
                            <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-5 h-0.5 bg-blue-400 rounded-full"></span>
                        @endif
                    </a>
                    <a href="{{ route('rankings') }}" class="relative text-xs font-bold uppercase tracking-wider transition-all duration-200 {{ request()->routeIs('rankings*') ? 'text-blue-400 font-extrabold' : 'text-theme-secondary hover:text-theme hover:scale-105' }}" {{ request()->routeIs('rankings*') ? 'aria-current="page"' : '' }}>
                        {{ __('common.nav_rankings') }}
                        @if(request()->routeIs('rankings*'))
                            <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-5 h-0.5 bg-blue-400 rounded-full"></span>
                        @endif
                    </a>
                    <a href="{{ route('profile') }}" class="relative text-xs font-bold uppercase tracking-wider transition-all duration-200 {{ request()->routeIs('profile*') || request()->routeIs('account.*') ? 'text-blue-400 font-extrabold' : 'text-theme-secondary hover:text-theme hover:scale-105' }}" {{ request()->routeIs('profile*') || request()->routeIs('account.*') ? 'aria-current="page"' : '' }}>
                        {{ __('common.nav_profile') }}
                        @if(request()->routeIs('profile*') || request()->routeIs('account.*'))
                            <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-5 h-0.5 bg-blue-400 rounded-full"></span>
                        @endif
                    </a>
                    <a href="{{ route('about') }}" class="relative text-xs font-bold uppercase tracking-wider transition-all duration-200 {{ request()->routeIs('about*') ? 'text-blue-400 font-extrabold' : 'text-theme-secondary hover:text-theme hover:scale-105' }}" {{ request()->routeIs('about*') ? 'aria-current="page"' : '' }}>
                        {{ __('common.nav_about') }}
                        @if(request()->routeIs('about*'))
                            <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-5 h-0.5 bg-blue-400 rounded-full"></span>
                        @endif
                    </a>
                    <a href="{{ route('contact') }}" class="relative text-xs font-bold uppercase tracking-wider transition-all duration-200 {{ request()->routeIs('contact*') ? 'text-blue-400 font-extrabold' : 'text-theme-secondary hover:text-theme hover:scale-105' }}" {{ request()->routeIs('contact*') ? 'aria-current="page"' : '' }}>
                        {{ __('common.nav_contact') }}
                        @if(request()->routeIs('contact*'))
                            <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-5 h-0.5 bg-blue-400 rounded-full"></span>
                        @endif
                    </a>
                    @auth
                        @if(auth()->user()->is_admin)
                            <a href="{{ route('admin.dashboard') }}" class="relative text-xs font-bold uppercase tracking-wider transition-all duration-200 {{ request()->routeIs('admin.*') ? 'text-teal-400 font-extrabold' : 'text-theme-secondary hover:text-theme hover:scale-105' }}" {{ request()->routeIs('admin.*') ? 'aria-current="page"' : '' }}>
                                {{ __('common.nav_admin') }}
                                @if(request()->routeIs('admin.*'))
                                    <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-5 h-0.5 bg-teal-400 rounded-full"></span>
                                @endif
                            </a>
                        @endif
                    @endauth

                    <!-- Desktop Language Switcher -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @click.outside="open = false" class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-theme-secondary hover:text-theme transition-all px-2 py-1.5 rounded-lg hover:bg-white/5" aria-label="{{ __('common.language') }}" aria-haspopup="true" :aria-expanded="open">
                            <span class="text-base leading-none">{{ ['pt' => '🇵🇹', 'en' => '🇬🇧', 'es' => '🇪🇸', 'fr' => '🇫🇷'][app()->getLocale()] ?? '🇵🇹' }}</span>
                            <svg class="w-3 h-3 transition-transform duration-200" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-1 w-36 bg-theme-card border border-theme-medium rounded-xl shadow-xl overflow-hidden z-50">
                            @foreach(['pt' => '🇵🇹', 'en' => '🇬🇧', 'es' => '🇪🇸', 'fr' => '🇫🇷'] as $code => $flag)
                                <form method="POST" action="{{ route('locale.switch', $code) }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left flex items-center gap-2 px-3.5 py-2.5 text-xs font-semibold transition-colors hover:bg-white/5 {{ app()->getLocale() === $code ? 'text-blue-400 bg-blue-500/5' : 'text-theme-secondary' }}">
                                        <span class="text-base">{{ $flag }}</span>
                                        <span>{{ __("common.lang_{$code}") }}</span>
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                </nav>

                <!-- Right Controls: Theme Toggle, Score and Auth -->
                <div class="flex items-center gap-1 sm:gap-2 shrink-0">
                    <!-- Mobile Language Switcher -->
                    <div x-data="{ open: false }" class="relative md:hidden">
                        <button @click="open = !open" @click.outside="open = false" class="theme-toggle-btn touch-target" aria-label="{{ __('common.language') }}" aria-haspopup="true" :aria-expanded="open">
                            <span class="text-lg leading-none">{{ ['pt' => '🇵🇹', 'en' => '🇬🇧', 'es' => '🇪🇸', 'fr' => '🇫🇷'][app()->getLocale()] ?? '🇵🇹' }}</span>
                        </button>
                        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-1 w-36 bg-theme-card border border-theme-medium rounded-xl shadow-xl overflow-hidden z-50">
                            @foreach(['pt' => '🇵🇹', 'en' => '🇬🇧', 'es' => '🇪🇸', 'fr' => '🇫🇷'] as $code => $flag)
                                <form method="POST" action="{{ route('locale.switch', $code) }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left flex items-center gap-2 px-3.5 py-2.5 text-xs font-semibold transition-colors hover:bg-white/5 {{ app()->getLocale() === $code ? 'text-blue-400 bg-blue-500/5' : 'text-theme-secondary' }}">
                                        <span class="text-base">{{ $flag }}</span>
                                        <span>{{ __("common.lang_{$code}") }}</span>
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>

                    <!-- Push Notifications Toggle (desktop only) -->
                    @auth
                        <button x-data="pushHandler()"
                                x-on:click="toggle()"
                                x-show="ready"
                                x-cloak
                                class="theme-toggle-btn touch-target relative hidden sm:inline-flex"
                                :title="subscribed ? '{{ __('common.push_enabled') }}' : '{{ __('common.push_enable') }}'"
                                :class="subscribed ? 'text-blue-400' : 'text-theme-secondary'">
                            <span class="text-lg" x-text="subscribed ? '🔔' : '🔕'"></span>
                        </button>
                    @endauth

                    <!-- Theme Toggle -->
                    <button onclick="toggleAppTheme()" class="theme-toggle-btn touch-target" aria-label="{{ __('common.theme_toggle') }}" title="{{ __('common.theme_toggle') }}">
                        <span class="theme-toggle-dark-icon" style="font-size: 18px; line-height: 1; transition: transform 0.3s ease;" x-on:click="$el.style.transform = 'rotate(360deg)'">🌙</span>
                        <span class="theme-toggle-light-icon" style="font-size: 18px; line-height: 1; transition: transform 0.3s ease;">☀️</span>
                    </button>
                    @auth
                        <span class="hidden sm:inline-flex text-xs sm:text-sm bg-gradient-to-r from-yellow-500 to-amber-500 text-slate-950 font-bold px-1.5 sm:px-2.5 py-0.5 rounded-full shadow-sm whitespace-nowrap animate-scale-in" aria-label="{{ trans_choice('common.nav_score_label', auth()->user()->score, ['score' => auth()->user()->score]) }}">
                            <span aria-hidden="true">🏆</span> {{ auth()->user()->score }}
                        </span>
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" @click.outside="open = false" class="flex items-center text-xs sm:text-sm font-semibold text-theme bg-theme-card border border-theme-medium px-1.5 sm:px-2.5 py-1.5 rounded-lg truncate max-w-[60px] sm:max-w-[120px] transition-all hover:border-blue-500/40 cursor-pointer" aria-label="{{ __('common.nav_profile') }}" aria-haspopup="true" :aria-expanded="open">
                                <span class="sm:hidden" aria-hidden="true">👤</span><span class="hidden sm:inline"><span aria-hidden="true">👤</span> {{ Str::limit(auth()->user()->name, 8, '') }}</span>
                            </button>
                            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-1.5 w-44 bg-theme-card border border-theme-medium rounded-xl shadow-xl overflow-hidden z-50">
                                <a href="{{ route('profile') }}" @click="open = false" class="flex items-center gap-2.5 w-full text-left px-3.5 py-2.5 text-sm font-semibold transition-colors hover:bg-white/5 text-theme">
                                    <span aria-hidden="true">👤</span> {{ __('common.nav_profile') }}
                                </a>
                                <hr class="border-theme-subtle">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex items-center gap-2.5 w-full text-left px-3.5 py-2.5 text-sm font-semibold transition-colors hover:bg-white/5 text-red-400">
                                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                                        {{ __('common.nav_logout') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('profile') }}" class="text-xs sm:text-sm font-semibold text-white bg-blue-600 hover:bg-blue-500 px-3 py-1.5 sm:py-1.5 rounded-lg transition-all shadow-md touch-target inline-flex items-center hover:shadow-lg hover:shadow-blue-500/25 active:scale-95">
                            {{ __('common.nav_login') }}
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main id="main-content" class="flex-1 w-full max-w-7xl mx-auto px-3 sm:px-4 md:px-6 py-5 sm:py-6 pb-32 md:pb-12 pb-safe" role="main">
            {{ $slot }}

            <!-- Footer Area -->
            <footer class="w-full border-t border-theme-subtle py-6 mt-12 text-theme-muted text-xs space-y-4" role="contentinfo">
                <div class="p-4 rounded-xl border border-red-500/20 bg-red-950/10 text-red-300 leading-relaxed shadow-sm" role="alert">
                    <span class="font-bold text-red-400 uppercase tracking-wide block mb-1"><span aria-hidden="true">⚠️</span> {{ __('common.footer_disclaimer_title') }}:</span>
                    {{ __('common.footer_disclaimer') }}
                </div>

                <div class="flex items-center justify-between border-t border-theme-subtle pt-3">
                    <div>&copy; {{ date('Y') }} {{ __('common.footer_copyright') }}</div>
                    <div class="flex gap-2">
                        <a href="{{ route('about') }}" class="hover:text-theme transition-colors">{{ __('common.footer_about') }}</a>
                        <span aria-hidden="true">&middot;</span>
                        <a href="{{ route('contact') }}" class="hover:text-theme transition-colors">{{ __('common.footer_contact') }}</a>
                        <span aria-hidden="true">&middot;</span>
                        <a href="{{ route('terms') }}" class="hover:text-theme transition-colors">{{ __('common.footer_terms') }}</a>
                        <span aria-hidden="true">&middot;</span>
                        <a href="{{ route('privacy') }}" class="hover:text-theme transition-colors">{{ __('common.footer_privacy') }}</a>
                    </div>
                </div>
            </footer>
        </main>

        <!-- PWA Install Banner -->
        <div x-data="pwaInstallHandler()"
             x-show="showInstall && !dismissed"
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-4"
             class="fixed bottom-20 md:bottom-4 left-0 right-0 z-[55] mx-auto max-w-sm px-4 pb-safe"
             role="alert">
            <div class="bg-theme-card backdrop-blur-xl border border-blue-500/20 rounded-2xl shadow-2xl p-4 flex items-center gap-3">
                <img src="/icon-192.png" alt="CheckPraia" class="w-12 h-12 rounded-xl shrink-0 shadow-lg">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-theme truncate">{{ __('common.pwa_install_title') }}</p>
                    <p class="text-xs text-theme-secondary leading-tight mt-0.5">{{ __('common.pwa_install_description') }}</p>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                    <button @click="dismiss()" class="text-xs font-semibold text-theme-secondary hover:text-theme px-2.5 py-1.5 rounded-lg hover:bg-white/5 transition-colors">
                        {{ __('common.pwa_install_dismiss') }}
                    </button>
                    <button @click="install()" class="text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 px-3.5 py-2 rounded-xl shadow-lg shadow-blue-600/20 transition-all active:scale-95">
                        {{ __('common.pwa_install_button') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Sticky Bottom Ad Banner (mobile only) -->
        <x-ads.slot slot="sticky_bottom" className="fixed bottom-14 left-0 right-0 z-40 md:hidden pb-safe" />

        <!-- Sticky Bottom Navigation Bar for Mobile Only (PWA Feel) -->
        <nav class="fixed bottom-0 left-0 right-0 z-50 bg-theme-nav backdrop-blur-lg border-t border-theme-medium flex justify-around items-center py-1 px-0.5 pb-safe md:hidden pl-safe pr-safe shadow-[0_-4px_20px_rgba(0,0,0,0.15)]" aria-label="{{ __('common.nav_mobile_map') }}">
            @php $navItems = [
                ['route' => 'home', 'icon' => '🗺️', 'label' => 'nav_mobile_map', 'pattern' => 'home*', 'color' => 'blue'],
                ['route' => 'rankings', 'icon' => '🏆', 'label' => 'nav_mobile_rankings', 'pattern' => 'rankings*', 'color' => 'blue'],
                ['route' => 'profile', 'icon' => '👤', 'label' => 'nav_mobile_profile', 'pattern' => 'profile*|account.*', 'color' => 'blue'],
                ['route' => 'about', 'icon' => 'ℹ️', 'label' => 'nav_mobile_about', 'pattern' => 'about*', 'color' => 'blue'],
            ]; @endphp
            @foreach($navItems as $item)
                @php $active = request()->routeIs(explode('|', $item['pattern'])); @endphp
                <a href="{{ route($item['route']) }}" class="relative flex flex-col items-center gap-0 text-[9px] min-w-0 flex-1 px-0.5 py-0.5 transition-all duration-200 {{ $active ? 'text-blue-400 font-bold' : 'text-theme-secondary hover:text-theme' }}" {{ $active ? 'aria-current="page"' : '' }}>
                    <span class="text-base leading-none transition-transform duration-200" aria-hidden="true">{{ $item['icon'] }}</span>
                    <span class="truncate w-full text-center leading-tight">{{ __("common.{$item['label']}") }}</span>
                    @if($active)
                        <span class="absolute -top-0 left-1/2 -translate-x-1/2 w-5 h-0.5 bg-blue-400 rounded-full"></span>
                    @endif
                </a>
            @endforeach
            @auth
                @if(auth()->user()->is_admin)
                    @php $active = request()->routeIs('admin.*'); @endphp
                    <a href="{{ route('admin.dashboard') }}" class="relative flex flex-col items-center gap-0 text-[9px] min-w-0 flex-1 px-0.5 py-0.5 transition-all duration-200 {{ $active ? 'text-teal-400 font-bold' : 'text-theme-secondary hover:text-theme' }}" {{ $active ? 'aria-current="page"' : '' }}>
                        <span class="text-base leading-none transition-transform duration-200" aria-hidden="true">⚙️</span>
                        <span class="truncate w-full text-center leading-tight">{{ __('common.nav_mobile_admin') }}</span>
                        @if($active)
                            <span class="absolute -top-0 left-1/2 -translate-x-1/2 w-5 h-0.5 bg-teal-400 rounded-full"></span>
                        @endif
                    </a>
                @endif
            @endauth
            @guest
                @php $active = request()->routeIs('login'); @endphp
                <a href="{{ route('login') }}" class="relative flex flex-col items-center gap-0 text-[9px] min-w-0 flex-1 px-0.5 py-0.5 transition-all duration-200 {{ $active ? 'text-blue-400 font-bold' : 'text-theme-secondary hover:text-theme' }}">
                    <span class="text-base leading-none transition-transform duration-200" aria-hidden="true">🔑</span>
                    <span class="truncate w-full text-center leading-tight">{{ __('common.nav_login') }}</span>
                    @if($active)
                        <span class="absolute -top-0 left-1/2 -translate-x-1/2 w-5 h-0.5 bg-blue-400 rounded-full"></span>
                    @endif
                </a>
            @endguest
        </nav>

        <!-- Livewire Loading Indicator -->
        <div wire:loading class="livewire-loading-bar"></div>

        <!-- Global Toast (share/copy feedback) -->
        <div x-data="appToast()" x-show="show" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4" class="fixed bottom-28 left-1/2 -translate-x-1/2 z-[60] max-w-xs w-full mx-auto px-4 pointer-events-none" role="status">
            <div class="bg-slate-800/90 backdrop-blur-md text-white text-sm font-medium px-4 py-3 rounded-xl shadow-2xl border border-slate-600/30 flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span x-text="message" class="flex-1"></span>
            </div>
        </div>
    </div>

    <!-- Leaflet Map Script (deferred to avoid blocking) -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="" defer></script>

    <!-- ARIA live region for dynamic notifications -->
    <div aria-live="polite" aria-atomic="true" class="sr-only" id="notification-aria"></div>

    <!-- PWA Service Worker Registration -->
    <script>
        document.addEventListener('notify', (e) => {
            const el = document.getElementById('notification-aria');
            if (el && e.detail) {
                el.textContent = e.detail.message || '';
            }
        });

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(() => {});
            });
        }
    </script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('themeHandler', () => ({
                init() {
                    const saved = localStorage.getItem('checkpraia-theme');
                    document.documentElement.setAttribute('data-theme', saved || 'dark');
                }
            }));

            Alpine.data('notificationPrompter', () => ({
                show: false,
                dismissed: false,

                init() {
                    if (localStorage.getItem('checkpraia-notification-prompt-dismissed')) {
                        this.dismissed = true;
                        return;
                    }
                    if (Notification.permission === 'granted') return;
                    if (Notification.permission === 'denied') return;
                    window.addEventListener('report-submitted', () => {
                        setTimeout(() => { if (!this.dismissed) this.show = true; }, 2000);
                    }, { once: true });
                },

                dismiss() {
                    this.show = false;
                    this.dismissed = true;
                    localStorage.setItem('checkpraia-notification-prompt-dismissed', '1');
                },

                async enable() {
                    this.show = false;
                    this.dismissed = true;
                    localStorage.setItem('checkpraia-notification-prompt-dismissed', '1');
                    const handler = Alpine.$data(document.querySelector('[x-data="pushHandler"]'));
                    if (handler && !handler.subscribed) {
                        await handler.subscribe();
                    }
                }
            }));

            Alpine.data('pushHandler', () => ({
                ready: false,
                subscribed: false,
                loading: false,

                init() {
                    if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;
                    this.checkStatus();
                },

                async checkStatus() {
                    try {
                        const res = await fetch('/push/status');
                        const data = await res.json();
                        this.subscribed = data.subscribed;
                    } catch {}
                    this.ready = true;
                },

                async toggle() {
                    if (this.loading) return;
                    this.loading = true;

                    try {
                        if (this.subscribed) {
                            await this.unsubscribe();
                        } else {
                            await this.subscribe();
                        }
                    } catch (e) {
                        console.error('Push error:', e);
                    }

                    this.loading = false;
                },

                async subscribe() {
                    let permission = Notification.permission;
                    if (permission === 'denied') return;

                    if (permission === 'default') {
                        permission = await Notification.requestPermission();
                        if (permission !== 'granted') return;
                    }

                    const registration = await navigator.serviceWorker.ready;
                    let subscription = await registration.pushManager.getSubscription();

                    if (!subscription) {
                        const vapidKey = '{{ config('webpush.vapid.public_key') }}';
                        subscription = await registration.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: this.urlBase64ToUint8Array(vapidKey),
                        });
                    }

                    const sub = subscription.toJSON();
                    let latitude = null;
                    let longitude = null;

                    if ('geolocation' in navigator) {
                        try {
                            const pos = await new Promise((resolve, reject) => {
                                navigator.geolocation.getCurrentPosition(resolve, reject, {
                                    timeout: 5000, maximumAge: 300000, enableHighAccuracy: false,
                                });
                            });
                            latitude = pos.coords.latitude;
                            longitude = pos.coords.longitude;
                        } catch {}
                    }

                    const res = await fetch('/push/subscribe', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({
                            endpoint: sub.endpoint,
                            public_key: sub.keys?.p256dh || null,
                            auth_token: sub.keys?.auth || null,
                            content_encoding: 'aesgcm',
                            latitude,
                            longitude,
                        }),
                    });

                    if (res.ok) {
                        this.subscribed = true;

                        fetch('/push/test', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ endpoint: sub.endpoint }),
                        });
                    }
                },

                async unsubscribe() {
                    const registration = await navigator.serviceWorker.ready;
                    const subscription = await registration.pushManager.getSubscription();

                    if (subscription) {
                        const sub = subscription.toJSON();

                        await fetch('/push/unsubscribe', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ endpoint: sub.endpoint }),
                        });

                        await subscription.unsubscribe();
                    }

                    this.subscribed = false;
                },

                urlBase64ToUint8Array(base64String) {
                    const padding = '='.repeat((4 - base64String.length % 4) % 4);
                    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
                    const rawData = window.atob(base64);
                    const output = new Uint8Array(rawData.length);
                    for (let i = 0; i < rawData.length; ++i) output[i] = rawData.charCodeAt(i);
                    return output;
                },
            }));

            Alpine.data('pwaInstallHandler', () => ({
                deferredPrompt: null,
                showInstall: false,
                dismissed: false,

                init() {
                    if (this._isStandalone()) return;

                    const dismissed = localStorage.getItem('checkpraia-pwa-dismissed');
                    if (dismissed) { this.dismissed = true; return; }

                    window.addEventListener('beforeinstallprompt', (e) => {
                        e.preventDefault();
                        this.deferredPrompt = e;
                        this.showInstall = true;
                    });

                    window.addEventListener('appinstalled', () => {
                        this.showInstall = false;
                        this.deferredPrompt = null;
                        localStorage.setItem('checkpraia-pwa-installed', '1');
                    });
                },

                _isStandalone() {
                    return window.matchMedia('(display-mode: standalone)').matches
                        || window.navigator.standalone === true
                        || localStorage.getItem('checkpraia-pwa-installed') === '1';
                },

                async install() {
                    if (!this.deferredPrompt) return;
                    this.deferredPrompt.prompt();
                    const result = await this.deferredPrompt.userChoice;
                    this.deferredPrompt = null;
                    this.showInstall = false;
                    if (result.outcome === 'accepted') {
                        localStorage.setItem('checkpraia-pwa-installed', '1');
                    }
                },

                dismiss() {
                    this.showInstall = false;
                    this.dismissed = true;
                    localStorage.setItem('checkpraia-pwa-dismissed', '1');
                }
            }));

            Alpine.data('appToast', () => ({
                show: false,
                message: '',
                timer: null,
                init() {
                    window.addEventListener('toast', (e) => {
                        if (this.timer) clearTimeout(this.timer);
                        this.message = e.detail.message;
                        this.show = true;
                        this.timer = setTimeout(() => { this.show = false; }, 3000);
                    });
                }
            }));

            Alpine.data('appShareHandler', () => ({
                async share() {
                    const url = '{{ url('/') }}';
                    const title = '{{ __('profile.share_app_title') }}';
                    const text = '{{ __('profile.share_app_text') }}';
                    const result = await shareViaAPI({ title, text, url });
                    if (result === 'copied') {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: '{{ __('profile.share_copied') }}' } }));
                    }
                },
                async downloadCard() {
                    const c = document.createElement('canvas'); c.width = 600; c.height = 314;
                    drawShareCard(c, {
                        tagline: '{{ __('common.site_description') }}',
                        body: '{{ __('profile.share_app_text') }}',
                    });
                    const a = document.createElement('a');
                    a.download = 'checkpraia-card.png';
                    a.href = c.toDataURL('image/png');
                    a.click();
                }
            }));

            Alpine.data('rankingShareHandler', () => ({
                position: null,
                score: null,
                username: null,
                showCard: false,
                init() {
                    const el = this.$el;
                    this.position = el.dataset.position;
                    this.score = el.dataset.score;
                    this.username = el.dataset.username;
                },
                async share() {
                    const url = '{{ url('/rankings') }}';
                    const title = '{{ __('rankings.share_ranking_title', ['position' => '']) }}'.replace(':position', this.position);
                    const text = '{{ __('rankings.share_ranking_text', ['position' => '', 'score' => '']) }}'
                        .replace(':position', this.position)
                        .replace(':score', this.score);
                    const result = await shareViaAPI({ title, text, url });
                    if (result === 'copied') {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: '{{ __('profile.share_copied') }}' } }));
                    }
                },
                toggleCard() {
                    this.showCard = !this.showCard;
                    if (this.showCard) this.$nextTick(() => this.renderPreview());
                },
                renderPreview() {
                    const c = document.getElementById('share-card-preview');
                    if (!c) return;
                    const orig = c.getAttribute('width') === '600' ? null : c;
                    const canvas = orig || document.createElement('canvas');
                    if (!orig) { canvas.width = 600; canvas.height = 314; }
                    drawShareCard(canvas, {
                        tagline: '{{ __('common.site_description') }}',
                        body: this.username + '\n# ' + this.position + '  |  ' + this.score + ' pts',
                        details: { label: '{{ __('rankings.share_my_rank') }}', value: '#' + this.position + '  ·  ' + this.score + ' pts' },
                    });
                    if (orig) return;
                    c.width = 600; c.height = 314;
                    c.getContext('2d').drawImage(canvas, 0, 0);
                },
                async downloadCard() {
                    const c = document.getElementById('share-card-preview');
                    if (!c) return;
                    const a = document.createElement('a');
                    a.download = 'checkpraia-ranking-' + this.position + '.png';
                    a.href = c.toDataURL('image/png');
                    a.click();
                    this.showCard = false;
                }
            }));
        });

        // Canvas Card Generator
        if (!CanvasRenderingContext2D.prototype.roundRect) {
            CanvasRenderingContext2D.prototype.roundRect = function(x, y, w, h, r) {
                if (r > w/2) r = w/2; if (r > h/2) r = h/2;
                this.moveTo(x + r, y); this.lineTo(x + w - r, y);
                this.quadraticCurveTo(x + w, y, x + w, y + r);
                this.lineTo(x + w, y + h - r);
                this.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
                this.lineTo(x + r, y + h);
                this.quadraticCurveTo(x, y + h, x, y + h - r);
                this.lineTo(x, y + r);
                this.quadraticCurveTo(x, y, x + r, y);
                return this;
            };
        }
        const drawShareCard = (canvas, opts) => {
            const ctx = canvas.getContext('2d');
            const w = canvas.width = 600, h = canvas.height = 314;
            const grad = ctx.createLinearGradient(0, 0, w, h);
            grad.addColorStop(0, '#1e3a5f'); grad.addColorStop(0.5, '#0f2b4a'); grad.addColorStop(1, '#0a1a2e');
            ctx.fillStyle = grad; ctx.fillRect(0, 0, w, h);
            // Accent line
            ctx.fillStyle = '#3b82f6'; ctx.fillRect(0, 0, 6, h);
            // Logo placeholder
            ctx.font = 'bold 42px sans-serif'; ctx.fillStyle = '#60a5fa'; ctx.fillText('🌊', 30, 80);
            ctx.font = 'bold 28px sans-serif'; ctx.fillStyle = '#f1f5f9'; ctx.fillText('CheckPraia', 90, 85);
            ctx.font = '14px sans-serif'; ctx.fillStyle = '#94a3b8'; ctx.fillText(opts.tagline || 'Bandeiras das Praias em Tempo Real', 90, 110);
            // Body
            if (opts.body) {
                ctx.font = '16px sans-serif'; ctx.fillStyle = '#e2e8f0';
                const lines = opts.body.split('\n');
                lines.forEach((l, i) => ctx.fillText(l, 30, 165 + i * 28));
            }
            // Detail box
            if (opts.details) {
                const bx = 30, by = h - 95, bw = w - 60, bh = 60;
                ctx.fillStyle = 'rgba(59,130,246,0.15)'; ctx.beginPath(); ctx.roundRect(bx, by, bw, bh, 12); ctx.fill();
                ctx.strokeStyle = 'rgba(59,130,246,0.3)'; ctx.lineWidth = 1; ctx.beginPath(); ctx.roundRect(bx, by, bw, bh, 12); ctx.stroke();
                ctx.font = 'bold 14px sans-serif'; ctx.fillStyle = '#93c5fd';
                ctx.fillText(opts.details.label, bx + 16, by + 24);
                ctx.font = 'bold 22px sans-serif'; ctx.fillStyle = '#f1f5f9';
                ctx.fillText(opts.details.value, bx + 16, by + 52);
                // URL at bottom-right
                ctx.font = '12px sans-serif'; ctx.fillStyle = '#64748b';
                ctx.fillText('checkpraia.pt', w - 140, by + 48);
            }
            // Bottom bar
            ctx.fillStyle = 'rgba(255,255,255,0.05)'; ctx.fillRect(0, h - 30, w, 30);
            ctx.font = '11px sans-serif'; ctx.fillStyle = '#64748b';
            ctx.fillText('checkpraia.pt', 30, h - 10);
        };

        // Share Handlers
        const shareViaAPI = async (data) => {
            if (navigator.share) {
                try { await navigator.share(data); return true; } catch {}
            }
            if (navigator.clipboard?.writeText) {
                await navigator.clipboard.writeText(data.url || data.text);
                return 'copied';
            }
            return false;
        };

        window.toggleAppTheme = function() {
            const current = document.documentElement.getAttribute('data-theme') || 'dark';
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('checkpraia-theme', next);
            const meta = document.querySelector('meta[name="color-scheme"]');
            if (meta) meta.setAttribute('content', next === 'dark' ? 'dark' : 'light');
            window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: next } }));
        };
    </script>

    @livewireScripts

    {{-- Notification permission prompt (shows after first report) --}}
    <div x-data="notificationPrompter"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-8"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-8"
         class="fixed bottom-24 sm:bottom-8 left-4 right-4 sm:left-auto sm:right-6 sm:w-96 z-50 glass-card rounded-2xl border border-blue-500/30 p-5 shadow-2xl shadow-blue-500/10"
         role="alert">
        <button @click="dismiss()" class="absolute top-3 right-3 text-slate-400 hover:text-slate-200 transition-colors p-1" aria-label="Fechar">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <div class="flex items-start gap-3">
            <span class="text-2xl shrink-0">🔔</span>
            <div>
                <p class="text-sm font-bold text-theme mb-1">{{ __('common.push_enable') }}</p>
                <p class="text-xs text-theme-secondary leading-relaxed">{{ __('common.push_enable_description') }}</p>
                <div class="flex gap-2 mt-3">
                    <button @click="enable()" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl transition-all active:scale-95">
                        {{ __('common.push_enable') }}
                    </button>
                    <button @click="dismiss()" class="px-4 py-2 text-xs text-slate-400 hover:text-slate-200 transition-colors font-medium">
                        {{ __('common.no_thanks') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
