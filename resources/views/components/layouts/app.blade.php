<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="themeHandler()">
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

    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2599903177483788"
            crossorigin="anonymous"></script>

    <title>@yield('title', __('common.site_name') . ' - ' . __('common.site_description'))</title>
    <meta name="description" content="@yield('meta_description', __('common.meta_description'))">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">

    <!-- Canonical URL -->
    <link rel="canonical" href="@yield('canonical', url()->current())">

    <!-- Hreflang / Alternate Language URLs (override per page for dynamic routes) -->
    @section('hreflang')
        @foreach(config('locales.supported', ['pt', 'en', 'es', 'fr']) as $locale)
            <link rel="alternate" hreflang="{{ $locale }}" href="{{ url($locale === 'pt' ? '' : "/{$locale}") }}">
        @endforeach
    @show
    <link rel="alternate" hreflang="x-default" href="{{ url('/') }}">

    <!-- Open Graph -->
    <meta property="og:title" content="@yield('og_title', __('common.site_name'))">
    <meta property="og:description" content="@yield('og_description', __('common.meta_description'))">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    <meta property="og:image" content="@yield('og_image', asset('logo.png'))">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="{{ __('common.site_name') }}">
    <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', __('common.site_name'))">
    <meta name="twitter:description" content="@yield('og_description', __('common.meta_description'))">
    <meta name="twitter:image" content="@yield('og_image', asset('logo.png'))">

    <!-- Fonts: Plus Jakarta Sans with display=swap to never block rendering -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://tile.openstreetmap.org">
    <link rel="preconnect" href="https://unpkg.com">
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet" media="print" onload="this.media='all'">

    <!-- Preconnect for map tile providers -->
    <link rel="preconnect" href="https://basemaps.cartocdn.com">
    <link rel="dns-prefetch" href="https://basemaps.cartocdn.com">
    <link rel="dns-prefetch" href="https://a.basemaps.cartocdn.com">
    <link rel="dns-prefetch" href="https://b.basemaps.cartocdn.com">
    <link rel="dns-prefetch" href="https://c.basemaps.cartocdn.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <!-- Leaflet CSS: only injected on pages that have a map (via stack) -->
    @stack('leaflet-css')
    @section('leaflet-inline')
    @show

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
                    'url' => asset('logo.png'),
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
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-base-gradient);
            color: var(--text-primary);
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

        [data-theme="light"] .text-slate-400 { color: #64748b; }
        [data-theme="light"] .text-slate-500 { color: #475569; }
        [data-theme="light"] .bg-white\/5,
        [data-theme="light"] .bg-white\/\[5\%\] { background: rgba(0, 0, 0, 0.04); }
        [data-theme="light"] .bg-slate-900\/60 { background: rgba(226, 232, 240, 0.8); }
        [data-theme="light"] .bg-slate-800\/80 { background: rgba(203, 213, 225, 0.8); }
        [data-theme="light"] .glass-card .text-slate-300,
        [data-theme="light"] .bg-theme-card .text-slate-300 { color: #475569; }
        [data-theme="light"] .glass-card .text-slate-200 { color: #334155; }
        [data-theme="light"] .glass-card .text-slate-400 { color: #475569; }
        [data-theme="light"] .bg-rose-950\/20 { background: rgba(254, 202, 202, 0.3); }
        [data-theme="light"] .text-rose-200 { color: #c53030; }
        [data-theme="light"] .text-red-300 { color: #c53030; }
        [data-theme="light"] .text-red-400 { color: #dc2626; }
        [data-theme="light"] .text-rose-300 { color: #e11d48; }
        [data-theme="light"] .text-rose-400 { color: #e11d48; }
        [data-theme="light"] .text-emerald-300 { color: #047857; }
        [data-theme="light"] .text-emerald-400 { color: #047857; }
        [data-theme="light"] .text-amber-300 { color: #b45309; }
        [data-theme="light"] .text-amber-400 { color: #b45309; }
        [data-theme="light"] .text-teal-300 { color: #0f766e; }
        [data-theme="light"] .text-teal-400 { color: #0f766e; }
        [data-theme="light"] .text-blue-300 { color: #2563eb; }
        [data-theme="light"] .text-blue-400 { color: #2563eb; }
        [data-theme="light"] .text-sky-400 { color: #0284c7; }
        [data-theme="light"] .text-indigo-400 { color: #4f46e5; }
        [data-theme="light"] .text-violet-400 { color: #7c3aed; }
        [data-theme="light"] .text-purple-400 { color: #9333ea; }
        [data-theme="light"] .text-yellow-400 { color: #a16207; }
        [data-theme="light"] .bg-red-950\/10 { background: rgba(254, 202, 202, 0.2); }
        [data-theme="light"] .bg-blue-950\/10 { background: rgba(219, 234, 254, 0.4); }
        [data-theme="light"] .text-slate-300 { color: #475569; }
        [data-theme="light"] .text-slate-200 { color: #334155; }
        [data-theme="light"] .bg-slate-900\/30 { background: rgba(226, 232, 240, 0.5); }
        [data-theme="light"] .bg-slate-950\/30 { background: rgba(226, 232, 240, 0.4); }
        [data-theme="light"] .bg-slate-950\/20 { background: rgba(226, 232, 240, 0.35); }
        [data-theme="light"] .bg-slate-900\/50 { background: rgba(226, 232, 240, 0.6); }
        [data-theme="light"] .border-white\/10 { border-color: rgba(0, 0, 0, 0.1); }
        [data-theme="light"] .border-white\/\[0\.08\] { border-color: rgba(0, 0, 0, 0.1); }
        [data-theme="light"] .bg-slate-800\/80,
        [data-theme="light"] .bg-slate-800 { background: rgba(203, 213, 225, 0.8); }
        [data-theme="light"] .bg-slate-900,
        [data-theme="light"] .bg-slate-900\/30 { background: rgba(226, 232, 240, 0.5); }
        [data-theme="light"] .bg-slate-950,
        [data-theme="light"] .bg-slate-950\/20,
        [data-theme="light"] .bg-slate-950\/30,
        [data-theme="light"] .bg-slate-950\/80 { background: rgba(226, 232, 240, 0.4); }
        [data-theme="light"] .border-slate-700\/60 { border-color: rgba(0, 0, 0, 0.1); }
        [data-theme="light"] .hover\:bg-slate-700\/80:hover { background: rgba(203, 213, 225, 0.9); }
        [data-theme="light"] .hover\:bg-slate-700\/60:hover { background: rgba(203, 213, 225, 0.7); }
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
        {{ __('common.skip_to_content') }}
    </a>

    <!-- Ocean wave decorations (global, all pages) -->
    <div class="wave-decoration fixed inset-x-0 bottom-0 h-64 sm:h-80 md:h-96 z-0 pointer-events-none select-none overflow-hidden" aria-hidden="true" style="opacity:0.25">
        <svg viewBox="0 0 1440 320" preserveAspectRatio="none" class="w-full h-full" fill="#3b82f6">
            <path d="M0,224L48,213.3C96,203,192,181,288,181.3C384,181,480,203,576,213.3C672,224,768,224,864,208C960,192,1056,160,1152,154.7C1248,149,1344,171,1392,181.3L1440,192L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"/>
        </svg>
    </div>
    <div class="wave-decoration fixed inset-x-0 bottom-0 h-72 sm:h-[26rem] md:h-[28rem] z-0 pointer-events-none select-none overflow-hidden" aria-hidden="true" style="opacity:0.18">
        <svg viewBox="0 0 1440 320" preserveAspectRatio="none" class="w-full h-full" fill="#06b6d4">
            <path d="M0,64L60,85.3C120,107,240,149,360,154.7C480,160,600,128,720,138.7C840,149,960,203,1080,208C1200,213,1320,171,1380,149.3L1440,128L1440,320L1380,320C1320,320,1200,320,1080,320C960,320,840,320,720,320C600,320,480,320,360,320C240,320,120,320,60,320L0,320Z"/>
        </svg>
    </div>
    <div class="wave-decoration fixed inset-x-0 bottom-0 h-[20rem] sm:h-[30rem] md:h-[32rem] z-0 pointer-events-none select-none overflow-hidden" aria-hidden="true" style="opacity:0.12">
        <svg viewBox="0 0 1440 320" preserveAspectRatio="none" class="w-full h-full" fill="#0ea5e9">
            <path d="M0,160L48,144C96,128,192,96,288,106.7C384,117,480,171,576,192C672,213,768,203,864,176C960,149,1056,107,1152,101.3C1248,96,1344,128,1392,144L1440,160L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"/>
        </svg>
    </div>

    <div class="w-full flex-1 flex flex-col relative overflow-x-clip">

        <!-- Header -->
        <header class="sticky top-0 z-50 bg-theme-header backdrop-blur-md border-b border-theme-subtle pt-safe" role="banner">
            <div class="w-full max-w-7xl mx-auto px-5 sm:px-6 md:px-8 py-3 flex items-center justify-between">
                <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2 group shrink-0" aria-label="{{ __('common.nav_home') }}">
                    <img src="{{ asset('logo.png') }}" alt="{{ __('common.site_name') }}" width="132" height="48" class="h-11 sm:h-12 w-auto transition-transform duration-300 group-hover:scale-105" fetchpriority="high">
                </a>
                <!-- Desktop Navigation Menu -->
                <nav class="hidden md:flex items-center gap-4 lg:gap-6" aria-label="{{ __('common.nav_map') }}">
                    @if(request()->routeIs('home*'))
                        <span class="relative text-xs font-bold uppercase tracking-wider text-blue-400" aria-current="page">
                            {{ __('common.nav_map') }}
                            <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-5 h-0.5 bg-blue-400 rounded-full"></span>
                        </span>
                    @else
                        <a href="{{ route('home') }}" wire:navigate class="relative text-xs font-bold uppercase tracking-wider transition-all duration-200 text-theme-secondary hover:text-theme hover:scale-105 focus:outline-none">
                            {{ __('common.nav_map') }}
                        </a>
                    @endif
                    <a href="{{ route('rankings') }}" wire:navigate class="relative text-xs font-bold uppercase tracking-wider transition-all duration-200 {{ request()->routeIs('rankings*') ? 'text-blue-400' : 'text-theme-secondary hover:text-theme hover:scale-105' }} focus:outline-none" {{ request()->routeIs('rankings*') ? 'aria-current="page"' : '' }}>
                        {{ __('common.nav_rankings') }}
                        @if(request()->routeIs('rankings*'))
                            <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-5 h-0.5 bg-blue-400 rounded-full"></span>
                        @endif
                    </a>
                    <a href="{{ route('profile') }}" wire:navigate class="relative text-xs font-bold uppercase tracking-wider transition-all duration-200 {{ request()->routeIs('profile*') || request()->routeIs('account.*') ? 'text-blue-400' : 'text-theme-secondary hover:text-theme hover:scale-105' }} focus:outline-none" {{ request()->routeIs('profile*') || request()->routeIs('account.*') ? 'aria-current="page"' : '' }}>
                        {{ __('common.nav_profile') }}
                        @if(request()->routeIs('profile*') || request()->routeIs('account.*'))
                            <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-5 h-0.5 bg-blue-400 rounded-full"></span>
                        @endif
                    </a>
                    <a href="{{ route('about') }}" wire:navigate class="relative text-xs font-bold uppercase tracking-wider transition-all duration-200 {{ request()->routeIs('about*') ? 'text-blue-400' : 'text-theme-secondary hover:text-theme hover:scale-105' }} focus:outline-none" {{ request()->routeIs('about*') ? 'aria-current="page"' : '' }}>
                        {{ __('common.nav_about') }}
                        @if(request()->routeIs('about*'))
                            <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-5 h-0.5 bg-blue-400 rounded-full"></span>
                        @endif
                    </a>
                    <a href="{{ route('contact') }}" wire:navigate class="relative text-xs font-bold uppercase tracking-wider transition-all duration-200 {{ request()->routeIs('contact*') ? 'text-blue-400' : 'text-theme-secondary hover:text-theme hover:scale-105' }} focus:outline-none" {{ request()->routeIs('contact*') ? 'aria-current="page"' : '' }}>
                        {{ __('common.nav_contact') }}
                        @if(request()->routeIs('contact*'))
                            <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-5 h-0.5 bg-blue-400 rounded-full"></span>
                        @endif
                    </a>
                    @auth
                        @if(auth()->user()->is_admin)
                            <a href="{{ route('admin.dashboard') }}" wire:navigate class="relative text-xs font-bold uppercase tracking-wider transition-all duration-200 {{ request()->routeIs('admin.*') ? 'text-teal-400' : 'text-theme-secondary hover:text-theme hover:scale-105' }} focus:outline-none" {{ request()->routeIs('admin.*') ? 'aria-current="page"' : '' }}>
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
                            <span class="text-xs font-bold tracking-widest">{{ strtoupper(app()->getLocale()) }}</span>
                            <svg class="w-3 h-3 transition-transform duration-200" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-1 w-36 bg-theme-card border border-theme-medium rounded-xl shadow-xl overflow-hidden z-50">
                            @foreach(config('locales.supported', ['pt', 'en', 'es', 'fr']) as $code)
                                <form method="POST" action="{{ route('locale.switch', $code) }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left flex items-center gap-2 px-3.5 py-2.5 text-xs font-semibold transition-colors hover:bg-white/5 {{ app()->getLocale() === $code ? 'text-blue-400 bg-blue-500/5' : 'text-theme-secondary' }}">
                                        <span class="text-base">{{ config('locales.flags.' . $code) }}</span>
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
                            <span class="text-xs font-bold tracking-widest">{{ strtoupper(app()->getLocale()) }}</span>
                        </button>
                        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-1 w-36 bg-theme-card border border-theme-medium rounded-xl shadow-xl overflow-hidden z-50">
                            @foreach(config('locales.supported', ['pt', 'en', 'es', 'fr']) as $code)
                                <form method="POST" action="{{ route('locale.switch', $code) }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left flex items-center gap-2 px-3.5 py-2.5 text-xs font-semibold transition-colors hover:bg-white/5 {{ app()->getLocale() === $code ? 'text-blue-400 bg-blue-500/5' : 'text-theme-secondary' }}">
                                        <span class="text-base">{{ config('locales.flags.' . $code) }}</span>
                                        <span>{{ __("common.lang_{$code}") }}</span>
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>

                    <!-- Theme Toggle Switch -->
                    <button x-data="{ dark: localStorage.getItem('checkpraia-theme') !== 'light' }"
                            @click="toggleAppTheme(); dark = !dark"
                            class="relative w-14 h-7 rounded-full transition-colors duration-300 shrink-0 focus:outline-none focus:ring-2 focus:ring-blue-500/50 flex items-center"
                            :class="dark ? 'bg-slate-600' : 'bg-amber-300'"
                            :aria-label="dark ? '{{ __('common.theme_toggle') }}' : '{{ __('common.theme_toggle') }}'"
                            role="switch"
                            :aria-checked="!dark">
                        <span class="absolute left-1 top-1/2 -translate-y-1/2 text-sm pointer-events-none z-10 leading-none" x-show="!dark">☀️</span>
                        <span class="absolute right-1 top-1/2 -translate-y-1/2 text-sm pointer-events-none z-10 leading-none" x-show="dark">🌙</span>
                        <span class="absolute top-0.5 left-0.5 w-6 h-6 rounded-full bg-white shadow transition-transform duration-300 z-20"
                              :class="dark ? 'translate-x-7' : 'translate-x-0'"></span>
                    </button>
                    @auth
                        <span class="hidden sm:inline-flex items-baseline gap-0.5 text-xs sm:text-sm bg-gradient-to-r from-yellow-500 to-amber-500 text-slate-950 font-bold px-1.5 sm:px-2.5 py-0.5 rounded-full shadow-sm whitespace-nowrap animate-scale-in" aria-label="{{ trans_choice('common.nav_score_label', auth()->user()->score, ['score' => auth()->user()->score]) }}">
                            <span>{{ auth()->user()->score }}</span><span class="text-[10px] font-bold opacity-80">pts</span>
                        </span>
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" @click.outside="open = false" class="flex items-center text-xs sm:text-sm font-semibold text-theme bg-theme-card border border-theme-medium px-2 sm:px-3 py-1.5 rounded-lg truncate max-w-[60px] sm:max-w-[220px] hover:border-blue-500/40 cursor-pointer focus:outline-none" aria-label="{{ __('common.nav_profile') }}" aria-haspopup="true" :aria-expanded="open">
                                <span class="sm:hidden" aria-hidden="true">👤</span><span class="hidden sm:inline flex items-center gap-1"><span aria-hidden="true">👤</span> <span class="truncate">{{ auth()->user()->name }}</span></span>
                            </button>
                            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-1.5 w-44 bg-theme-card border border-theme-medium rounded-xl shadow-xl overflow-hidden z-50">
                                <a href="{{ route('profile') }}" wire:navigate @click="open = false" class="flex items-center gap-2.5 w-full text-left px-3.5 py-2.5 text-sm font-semibold transition-colors hover:bg-white/5 text-theme focus:outline-none">
                                    <span aria-hidden="true">👤</span> {{ __('common.nav_profile') }}
                                </a>
                                <hr class="border-theme-subtle">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex items-center gap-2.5 w-full text-left px-3.5 py-2.5 text-sm font-semibold transition-colors hover:bg-white/5 text-red-400 focus:outline-none">
                                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                                        {{ __('common.nav_logout') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('profile') }}" wire:navigate class="text-xs sm:text-sm font-semibold text-white bg-blue-600 hover:bg-blue-500 px-3 py-1.5 sm:py-1.5 rounded-lg transition-all shadow-md touch-target inline-flex items-center hover:shadow-lg hover:shadow-blue-500/25 active:scale-95 focus:outline-none">
                            {{ __('common.nav_login') }}
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main id="main-content" class="flex-1 w-full max-w-7xl mx-auto px-3 sm:px-4 md:px-6 py-5 sm:py-6 pb-32 md:pb-12 pb-safe" role="main">
            {{ $slot }}
        </main>

        <!-- Footer Area -->
        <footer class="w-full max-w-7xl mx-auto px-3 sm:px-4 md:px-6 border-t border-theme-subtle py-6 mt-4 text-theme-muted text-xs space-y-4" role="contentinfo">
                <div class="p-4 rounded-xl border border-red-500/20 bg-red-950/10 text-red-300 leading-relaxed shadow-sm" role="alert">
                    <span class="font-bold text-red-400 uppercase tracking-wide block mb-1"><span aria-hidden="true">⚠️</span> {{ __('common.footer_disclaimer_title') }}:</span>
                    {{ __('common.footer_disclaimer') }}
                </div>

                <div class="flex items-center justify-between border-t border-theme-subtle pt-3">
                    <div>&copy; {{ date('Y') }} {{ __('common.footer_copyright') }}</div>
                    <div class="flex gap-2">
                        <a href="{{ route('about') }}" wire:navigate class="hover:text-theme transition-colors">{{ __('common.footer_about') }}</a>
                        <span aria-hidden="true">&middot;</span>
                        <a href="{{ route('contact') }}" wire:navigate class="hover:text-theme transition-colors">{{ __('common.footer_contact') }}</a>
                        <span aria-hidden="true">&middot;</span>
                        <a href="{{ route('terms') }}" wire:navigate class="hover:text-theme transition-colors">{{ __('common.footer_terms') }}</a>
                        <span aria-hidden="true">&middot;</span>
                        <a href="{{ route('privacy') }}" wire:navigate class="hover:text-theme transition-colors">{{ __('common.footer_privacy') }}</a>
                    </div>
                </div>
            </footer>

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

        <!-- Livewire Loading Indicator -->
        <div wire:loading class="livewire-loading-bar"></div>

        <!-- Global Toast (share/copy feedback) -->
        <div x-data="appToast()" x-show="show" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4" class="fixed bottom-28 left-1/2 -translate-x-1/2 z-[60] max-w-xs w-full mx-auto px-4 pointer-events-none" role="status">
            <div class="bg-theme-elevated/90 backdrop-blur-md text-theme text-sm font-medium px-4 py-3 rounded-xl shadow-2xl border border-theme-medium flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span x-text="message" class="flex-1"></span>
            </div>
        </div>
    </div>

    <!-- Mobile Bottom Navigation (outside relative wrapper for proper fixed positioning) -->
    <nav class="fixed bottom-0 inset-x-0 z-[60] md:hidden" style="padding-bottom: env(safe-area-inset-bottom, 0px); will-change: transform;" aria-label="{{ __('common.nav_mobile_map') }}">
        <div class="relative">
            <div class="absolute inset-0 bg-gradient-to-t from-[var(--bg-nav)] via-[var(--bg-nav)]/98 to-transparent pointer-events-none"></div>
            <div class="relative bg-[var(--bg-nav)]/95 backdrop-blur-2xl border-t border-white/[0.06]">
                <div class="flex items-stretch justify-around max-w-lg mx-auto px-1 pt-1 pb-0.5">
                    @php
                        $navItems = [
                            ['route' => 'home', 'label' => 'nav_mobile_map', 'pattern' => 'home*'],
                            ['route' => 'rankings', 'label' => 'nav_mobile_rankings', 'pattern' => 'rankings*'],
                            ['route' => 'profile', 'label' => 'nav_mobile_profile', 'pattern' => 'profile*|account.*'],
                            ['route' => 'about', 'label' => 'nav_mobile_about', 'pattern' => 'about*'],
                        ];
                    @endphp
                    @foreach($navItems as $item)
                        @php $active = request()->routeIs(explode('|', $item['pattern'])); @endphp
                        @if($active)
                            <span class="relative flex flex-col items-center justify-center gap-0 min-w-0 flex-1 py-1.5 text-blue-400" aria-current="page">
                                <span class="relative flex items-center justify-center w-7 h-7">
                                    @if($item['route'] === 'home')
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M20.5 3l-.16.03L15 5.1 9 3 3.36 4.9c-.21.07-.36.25-.36.48V20.5c0 .28.22.5.5.5l.16-.03L9 18.9l6 2.1 5.64-1.9c.21-.07.36-.25.36-.48V3.5c0-.28-.22-.5-.5-.5zM15 19l-6-2.11V5l6 2.11V19z"/></svg>
                                    @elseif($item['route'] === 'rankings')
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5z"/></svg>
                                    @elseif($item['route'] === 'profile')
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                    @elseif($item['route'] === 'about')
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                                    @endif
                                    <span class="absolute -top-0.5 left-1/2 -translate-x-1/2 w-4 h-[2.5px] bg-blue-400 rounded-full"></span>
                                </span>
                                <span class="text-[10px] font-bold leading-none tracking-wide">{{ __("common.{$item['label']}") }}</span>
                            </span>
                        @else
                            <a href="{{ route($item['route']) }}" wire:navigate class="relative flex flex-col items-center justify-center gap-0 min-w-0 flex-1 py-1.5 text-[var(--text-muted)] active:text-theme transition-colors duration-100">
                                <span class="flex items-center justify-center w-7 h-7 opacity-70 active:opacity-100 transition-opacity">
                                    @if($item['route'] === 'home')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                    @elseif($item['route'] === 'rankings')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3l4.068 2.034M5 3v4m0-4l4.068-2.034M5 3l4.068-2.034M19 3l-4.068 2.034M19 3v4m0-4l-4.068-2.034M19 3l-4.068-2.034M5 7v10m14-10v10M9 21h6M9 7h6m-6 5h6"/></svg>
                                    @elseif($item['route'] === 'profile')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    @elseif($item['route'] === 'about')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 16v-4m0-4h.01"/></svg>
                                    @endif
                                </span>
                                <span class="text-[10px] font-medium leading-none tracking-wide">{{ __("common.{$item['label']}") }}</span>
                            </a>
                        @endif
                    @endforeach
                    @auth
                        @if(auth()->user()->is_admin)
                            @php $active = request()->routeIs('admin.*'); @endphp
                            @if($active)
                                <span class="relative flex flex-col items-center justify-center gap-0 min-w-0 flex-1 py-1.5 text-teal-400" aria-current="page">
                                    <span class="relative flex items-center justify-center w-7 h-7">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58a.49.49 0 00.12-.61l-1.92-3.32a.49.49 0 00-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54a.484.484 0 00-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96a.49.49 0 00-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.07.62-.07.94s.02.64.07.94l-2.03 1.58a.49.49 0 00-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6A3.6 3.6 0 1115.6 12 3.61 3.61 0 0112 15.6z"/></svg>
                                        <span class="absolute -top-0.5 left-1/2 -translate-x-1/2 w-4 h-[2.5px] bg-teal-400 rounded-full"></span>
                                    </span>
                                    <span class="text-[10px] font-bold leading-none tracking-wide">{{ __('common.nav_mobile_admin') }}</span>
                                </span>
                            @else
                                <a href="{{ route('admin.dashboard') }}" wire:navigate class="relative flex flex-col items-center justify-center gap-0 min-w-0 flex-1 py-1.5 text-[var(--text-muted)] active:text-theme transition-colors duration-100">
                                    <span class="flex items-center justify-center w-7 h-7 opacity-70 active:opacity-100 transition-opacity">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </span>
                                    <span class="text-[10px] font-medium leading-none tracking-wide">{{ __('common.nav_mobile_admin') }}</span>
                                </a>
                            @endif
                        @endif
                    @endauth
                    @guest
                        @php $active = request()->routeIs('login'); @endphp
                        @if($active)
                            <span class="relative flex flex-col items-center justify-center gap-0 min-w-0 flex-1 py-1.5 text-blue-400" aria-current="page">
                                <span class="relative flex items-center justify-center w-7 h-7">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM12 17c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zM9 8V6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9z"/></svg>
                                    <span class="absolute -top-0.5 left-1/2 -translate-x-1/2 w-4 h-[2.5px] bg-blue-400 rounded-full"></span>
                                </span>
                                <span class="text-[10px] font-bold leading-none tracking-wide">{{ __('common.nav_login') }}</span>
                            </span>
                        @else
                            <a href="{{ route('login') }}" wire:navigate class="relative flex flex-col items-center justify-center gap-0 min-w-0 flex-1 py-1.5 text-[var(--text-muted)] active:text-theme transition-colors duration-100">
                                <span class="flex items-center justify-center w-7 h-7 opacity-70 active:opacity-100 transition-opacity">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                </span>
                                <span class="text-[10px] font-medium leading-none tracking-wide">{{ __('common.nav_login') }}</span>
                            </a>
                        @endif
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <!-- Leaflet JS: only loaded on pages that push to this stack (home, beach detail with map) -->
    @stack('leaflet-js')

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

    {{-- Capture beforeinstallprompt early, before Alpine.js is ready --}}
    <script>
        window._deferredPrompt = null;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            window._deferredPrompt = e;
        });
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

                    // Use globally captured prompt if beforeinstallprompt fired early
                    if (window._deferredPrompt) {
                        this.deferredPrompt = window._deferredPrompt;
                        this.showInstall = true;
                    }

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

            Alpine.data('rankingShareHandler', (params = {}) => ({
                position:    params.position    || null,
                score:       params.score       || null,
                username:    params.username    || null,
                beaches:     Array.isArray(params.beaches)
                                 ? params.beaches
                                 : (params.beaches ? Object.entries(params.beaches) : []),
                showCard:    false,
                cardLoading: false,

                init() { /* data loaded from params */ },

                get shareUrl()  { return '{{ url('/rankings') }}'; },
                get shareText() {
                    return '{{ __('rankings.share_ranking_text', ['position' => ':position', 'score' => ':score']) }}'
                        .replace(':position', this.position)
                        .replace(':score', this.score);
                },

                toggleCard() {
                    this.showCard = !this.showCard;
                    if (this.showCard) {
                        this.cardLoading = true;
                        this.$nextTick(() => setTimeout(() => {
                            this.renderPreview();
                            this.cardLoading = false;
                        }, 80));
                    }
                },

                renderPreview() {
                    const canvas = document.getElementById('share-card-preview');
                    if (!canvas) return;
                    canvas.width = 1080; canvas.height = 1080;
                    // Normalise beaches to [[name, count], …]
                    let beachEntries = [];
                    if (Array.isArray(this.beaches)) {
                        beachEntries = this.beaches.map(b =>
                            Array.isArray(b) ? b : [b.name ?? String(b), b.confirmations ?? 1]);
                    } else if (this.beaches && typeof this.beaches === 'object') {
                        beachEntries = Object.entries(this.beaches);
                    }
                    drawRankingShareCard(canvas, {
                        username: this.username,
                        position: this.position,
                        score:    this.score,
                        beaches:  beachEntries,
                    });
                },

                async getBlob() {
                    const canvas = document.getElementById('share-card-preview');
                    return canvas ? new Promise(r => canvas.toBlob(r, 'image/png')) : null;
                },

                async nativeShare() {
                    const blob = await this.getBlob();
                    if (blob && navigator.share) {
                        try {
                            await navigator.share({
                                files: [new File([blob], 'checkpraia-ranking.png', { type: 'image/png' })],
                                title: 'CheckPraia — Ranking #' + this.position,
                                text:  this.shareText,
                            });
                            return;
                        } catch(e) {}
                    }
                    if (navigator.clipboard?.writeText) {
                        await navigator.clipboard.writeText(this.shareText + ' ' + this.shareUrl);
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: '{{ __('profile.share_copied') }}' } }));
                    }
                },

                async shareTo(platform) {
                    const t = encodeURIComponent(this.shareText + ' ' + this.shareUrl);
                    const u = encodeURIComponent(this.shareUrl);
                    if (platform === 'instagram') return this.nativeShare();
                    const urls = {
                        whatsapp: `https://wa.me/?text=${t}`,
                        facebook: `https://www.facebook.com/sharer/sharer.php?u=${u}&quote=${encodeURIComponent(this.shareText)}`,
                        x:        `https://x.com/intent/tweet?text=${t}`,
                    };
                    if (urls[platform]) window.open(urls[platform], '_blank', 'noopener,noreferrer,width=600,height=500');
                },

                async downloadCard() {
                    const canvas = document.getElementById('share-card-preview');
                    if (!canvas) return;
                    const a = document.createElement('a');
                    a.download = 'checkpraia-ranking-' + this.position + '.png';
                    a.href = canvas.toDataURL('image/png');
                    a.click();
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: '{{ __('profile.share_card_download') }}' } }));
                },
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
        if (!window.drawShareCard) window.drawShareCard = (canvas, opts) => {
            const ctx = canvas.getContext('2d');
            const w = canvas.width = 600, h = canvas.height = 314;
            const grad = ctx.createLinearGradient(0, 0, w, h);
            grad.addColorStop(0, '#1e3a5f'); grad.addColorStop(0.5, '#0f2b4a'); grad.addColorStop(1, '#0a1a2e');
            ctx.fillStyle = grad; ctx.fillRect(0, 0, w, h);
            ctx.fillStyle = '#3b82f6'; ctx.fillRect(0, 0, 6, h);
            ctx.font = 'bold 42px sans-serif'; ctx.fillStyle = '#60a5fa'; ctx.fillText('🌊', 30, 80);
            ctx.font = 'bold 28px sans-serif'; ctx.fillStyle = '#f1f5f9'; ctx.fillText('CheckPraia', 90, 85);
            ctx.font = '14px sans-serif'; ctx.fillStyle = '#94a3b8'; ctx.fillText(opts.tagline || 'Bandeiras das Praias em Tempo Real', 90, 110);
            if (opts.body) { ctx.font = '16px sans-serif'; ctx.fillStyle = '#e2e8f0'; opts.body.split('\n').forEach((l, i) => ctx.fillText(l, 30, 165 + i * 28)); }
            if (opts.details) {
                const bx = 30, by = h - 95, bw = w - 60, bh = 60;
                ctx.fillStyle = 'rgba(59,130,246,0.15)'; ctx.beginPath(); ctx.roundRect(bx,by,bw,bh,12); ctx.fill();
                ctx.strokeStyle = 'rgba(59,130,246,0.3)'; ctx.lineWidth = 1; ctx.beginPath(); ctx.roundRect(bx,by,bw,bh,12); ctx.stroke();
                ctx.font = 'bold 14px sans-serif'; ctx.fillStyle = '#93c5fd'; ctx.fillText(opts.details.label, bx+16, by+24);
                ctx.font = 'bold 22px sans-serif'; ctx.fillStyle = '#f1f5f9'; ctx.fillText(opts.details.value, bx+16, by+52);
                ctx.font = '12px sans-serif'; ctx.fillStyle = '#64748b'; ctx.fillText('checkpraia.pt', w-140, by+48);
            }
            ctx.fillStyle = 'rgba(255,255,255,0.05)'; ctx.fillRect(0, h-30, w, 30);
            ctx.font = '11px sans-serif'; ctx.fillStyle = '#64748b'; ctx.fillText('checkpraia.pt', 30, h-10);
        };

        // ============================================================
        // Premium Ranking Share Card — 1080×1080
        // Vertical map (canvas baseline notation):
        //   TOP BAR   Y  0–7
        //   LOGO+BADGE Y 20–150
        //   DIVIDER   Y  158
        //   TEXT      @username:193  headline:252  subtitle:296
        //   NUMBER    baseline 545  (font 195px → top≈405  bottom≈584)
        //   STATS     Y 620–755
        //   BEACHES   Y 776–930
        //   FOOTER    Y 965–1080
        // ============================================================
        window.drawRankingShareCard = (canvas, opts) => {
            const S = 1080, PAD = 66;
            canvas.width = S; canvas.height = S;

            const pos      = parseInt(opts.position) || 1;
            const score    = String(opts.score || '0');
            const username = (opts.username && String(opts.username).trim()) ? String(opts.username).trim() : '—';
            const beaches  = Array.isArray(opts.beaches) ? opts.beaches : [];

            const medal = pos === 1 ? { color:'#fbbf24', glow:'rgba(251,191,36,0.60)', label:'CAMPEÃO 🥇' }
                        : pos === 2 ? { color:'#e2e8f0', glow:'rgba(226,232,240,0.45)', label:'VICE-CAMPEÃO 🥈' }
                        : pos === 3 ? { color:'#fb923c', glow:'rgba(251,146,60,0.50)',  label:'3.º LUGAR 🥉' }
                        : pos <= 10 ? { color:'#38bdf8', glow:'rgba(56,189,248,0.45)',  label:'TOP 10 🔥' }
                        : pos <= 50 ? { color:'#a78bfa', glow:'rgba(167,139,250,0.40)', label:'TOP 50 ⚡' }
                        :             { color:'#60a5fa', glow:'rgba(96,165,250,0.35)',  label:'RANKING 🌊' };

            const rgba = (hex, a) => {
                const r=parseInt(hex.slice(1,3),16), g=parseInt(hex.slice(3,5),16), b=parseInt(hex.slice(5,7),16);
                return `rgba(${r},${g},${b},${a})`;
            };

            const _draw = (logo) => {
                const ctx = canvas.getContext('2d');

                // 1. Background
                const bg = ctx.createLinearGradient(0,0,S*0.6,S);
                bg.addColorStop(0,'#020c1b'); bg.addColorStop(0.4,'#071525');
                bg.addColorStop(0.8,'#0b1e36'); bg.addColorStop(1,'#040e1c');
                ctx.fillStyle = bg; ctx.fillRect(0,0,S,S);

                // 2. Atmospheric orbs
                [[S*0.85,S*0.10,S*0.55,'rgba(37,99,235,0.20)'],
                 [S*0.12,S*0.90,S*0.42,'rgba(16,185,129,0.16)'],
                 [S*0.22,S*0.50,S*0.35, rgba(medal.color,0.09)]
                ].forEach(([cx,cy,r,c]) => {
                    const rg = ctx.createRadialGradient(cx,cy,0,cx,cy,r);
                    rg.addColorStop(0,c); rg.addColorStop(1,'rgba(0,0,0,0)');
                    ctx.fillStyle = rg; ctx.fillRect(0,0,S,S);
                });

                // 3. Grid texture
                ctx.save(); ctx.globalAlpha=0.016; ctx.strokeStyle='#60a5fa'; ctx.lineWidth=1;
                for(let x=0;x<S;x+=54){ctx.beginPath();ctx.moveTo(x,0);ctx.lineTo(x,S);ctx.stroke();}
                for(let y=0;y<S;y+=54){ctx.beginPath();ctx.moveTo(0,y);ctx.lineTo(S,y);ctx.stroke();}
                ctx.restore();

                // 4. Speed lines
                ctx.save(); ctx.globalAlpha=0.042; ctx.strokeStyle='#38bdf8'; ctx.lineWidth=1;
                for(let i=0;i<7;i++){const lx=S*0.60+i*76; ctx.beginPath();ctx.moveTo(lx,0);ctx.lineTo(lx-S*0.22,S);ctx.stroke();}
                ctx.restore();

                // 5. Top accent bar
                const bar = ctx.createLinearGradient(0,0,S,0);
                bar.addColorStop(0,medal.color); bar.addColorStop(0.5,'#0ea5e9'); bar.addColorStop(1,'#10b981');
                ctx.fillStyle=bar; ctx.fillRect(0,0,S,7);

                // 6. Logo (zone Y=20–150)
                if(logo && logo.complete && logo.naturalWidth>0){
                    const sc=Math.min(240/logo.naturalWidth,110/logo.naturalHeight);
                    const lw=logo.naturalWidth*sc, lh=logo.naturalHeight*sc;
                    ctx.drawImage(logo, PAD, 20+(110-lh)/2, lw, lh);
                } else {
                    ctx.save();
                    ctx.font='bold 46px sans-serif'; ctx.fillStyle='#38bdf8';
                    ctx.fillText('🌊', PAD, 103);
                    ctx.font='bold 40px sans-serif';
                    const g2=ctx.createLinearGradient(PAD+56,0,PAD+310,0);
                    g2.addColorStop(0,'#f0f9ff'); g2.addColorStop(1,'#7dd3fc');
                    ctx.fillStyle=g2;
                    ctx.fillText('CheckPraia', PAD+56, 101);
                    ctx.restore();
                }

                // 7. Badge (top-right, Y=32–90)
                ctx.font='bold 27px sans-serif';
                const bW=ctx.measureText(medal.label).width+44, bH=52, bX=S-PAD-bW, bY=32;
                ctx.save();
                ctx.shadowColor=medal.glow; ctx.shadowBlur=20;
                const bBg=ctx.createLinearGradient(bX,bY,bX+bW,bY);
                bBg.addColorStop(0,rgba(medal.color,0.28)); bBg.addColorStop(1,rgba(medal.color,0.10));
                ctx.fillStyle=bBg; ctx.beginPath(); ctx.roundRect(bX,bY,bW,bH,26); ctx.fill();
                ctx.restore();
                ctx.strokeStyle=rgba(medal.color,0.55); ctx.lineWidth=1.5;
                ctx.beginPath(); ctx.roundRect(bX,bY,bW,bH,26); ctx.stroke();
                ctx.font='bold 25px sans-serif'; ctx.fillStyle=medal.color;
                ctx.textAlign='center'; ctx.fillText(medal.label, bX+bW/2, bY+35); ctx.textAlign='left';

                // 8. Divider Y=158
                const dv=ctx.createLinearGradient(PAD,0,S-PAD,0);
                dv.addColorStop(0,rgba(medal.color,0.5)); dv.addColorStop(0.5,'rgba(6,182,212,0.22)'); dv.addColorStop(1,rgba('#10b981',0.4));
                ctx.strokeStyle=dv; ctx.lineWidth=1.5;
                ctx.beginPath(); ctx.moveTo(PAD,158); ctx.lineTo(S-PAD,158); ctx.stroke();

                // 9. Text block
                // @username  baseline=193  font=34px  top≈168  bottom≈201
                // headline   baseline=252  font=54px  top≈213  bottom≈264
                // subtitle   baseline=296  font=28px  top≈275  bottom≈304
                // [gap to number top=405: 101px ✓]
                ctx.font='500 34px sans-serif'; ctx.fillStyle='#5a6d82';
                ctx.fillText('@'+username, PAD, 193);

                ctx.font='bold 54px sans-serif'; ctx.fillStyle='#f1f5f9';
                ctx.fillText('Estou no Ranking Nacional', PAD, 252);

                ctx.font='500 28px sans-serif'; ctx.fillStyle='#2e3f54';
                ctx.fillText('CheckPraia  ·  Portugal 🇵🇹', PAD, 296);

                // 10. Hero number
                // baseline=545  font=195px  top≈405  bottom≈584
                // [gap to stat cards top=620: 36px ✓]
                const NB=545, NFS=195;
                ctx.save();
                ctx.shadowColor=medal.glow; ctx.shadowBlur=85;
                ctx.font=`bold ${NFS}px sans-serif`;
                const numG=ctx.createLinearGradient(PAD,NB-NFS,PAD,NB);
                numG.addColorStop(0,medal.color); numG.addColorStop(0.6,rgba(medal.color,0.70)); numG.addColorStop(1,rgba(medal.color,0.28));
                ctx.fillStyle=numG; ctx.fillText('#'+pos, PAD, NB);
                ctx.restore();
                ctx.font=`bold ${NFS}px sans-serif`; ctx.fillStyle=numG;
                ctx.fillText('#'+pos, PAD, NB);

                // 11. Stat cards Y=620–755
                const SY=620, CH=135, CGP=22, CW=Math.floor((S-PAD*2-CGP)/2);
                const drawCard=(x,y,w,icon,value,label,col)=>{
                    const cg=ctx.createLinearGradient(x,y,x+w,y+CH);
                    cg.addColorStop(0,rgba(col,0.14)); cg.addColorStop(1,rgba(col,0.05));
                    ctx.fillStyle=cg; ctx.beginPath(); ctx.roundRect(x,y,w,CH,20); ctx.fill();
                    ctx.strokeStyle=rgba(col,0.28); ctx.lineWidth=1.5;
                    ctx.beginPath(); ctx.roundRect(x,y,w,CH,20); ctx.stroke();
                    ctx.font='36px sans-serif'; ctx.fillStyle='#fff'; ctx.fillText(icon, x+24, y+52);
                    ctx.font='bold 42px sans-serif'; ctx.fillStyle=col; ctx.fillText(value, x+24, y+100);
                    ctx.font='25px sans-serif'; ctx.fillStyle='#475569'; ctx.fillText(label, x+24, y+128);
                };
                drawCard(PAD,        SY,CW,'⭐',score+' pts',            'Pontuação',          medal.color);
                drawCard(PAD+CW+CGP, SY,CW,'🏖️',beaches.length+' praias','Praias confirmadas', '#10b981');

                // 12. Beach list Y=776–930
                const BLT=776, maxB=Math.min(beaches.length,3);
                if(maxB>0){
                    ctx.font='bold 22px sans-serif'; ctx.fillStyle='#2a3f58';
                    ctx.fillText('PRAIAS CONFIRMADAS', PAD, BLT);
                    for(let i=0;i<maxB;i++){
                        const entry=beaches[i];
                        const bName =Array.isArray(entry)?entry[0]:(entry.name??String(entry));
                        const bCount=Array.isArray(entry)?entry[1]:(entry.count??'');
                        const rowY  =BLT+40+i*54; // 816, 870, 924 ✓
                        ctx.fillStyle=medal.color; ctx.beginPath(); ctx.arc(PAD+6,rowY-6,5,0,Math.PI*2); ctx.fill();
                        ctx.font='28px sans-serif'; ctx.fillStyle='#b8cce0';
                        ctx.fillText(bName.length>36?bName.substring(0,34)+'…':bName, PAD+22, rowY);
                        if(bCount){
                            const cStr=bCount+'×', cW2=ctx.measureText(cStr).width+22, cX2=S-PAD-cW2-4;
                            ctx.font='bold 22px sans-serif';
                            ctx.fillStyle=rgba(medal.color,0.13); ctx.beginPath(); ctx.roundRect(cX2,rowY-24,cW2,32,16); ctx.fill();
                            ctx.fillStyle=medal.color; ctx.textAlign='center'; ctx.fillText(cStr,cX2+cW2/2,rowY-5); ctx.textAlign='left';
                        }
                    }
                    if(beaches.length>3){ ctx.font='24px sans-serif'; ctx.fillStyle='#2a3f58'; ctx.fillText(`+ ${beaches.length-3} mais…`,PAD+22,BLT+40+3*54); }
                }

                // 13. Footer Y=965–1080
                const footY=965, fH=S-footY;
                const fBg=ctx.createLinearGradient(0,footY-50,0,S);
                fBg.addColorStop(0,'rgba(2,12,27,0)'); fBg.addColorStop(0.4,'rgba(2,12,27,0.92)'); fBg.addColorStop(1,'rgba(2,12,27,0.99)');
                ctx.fillStyle=fBg; ctx.fillRect(0,footY-50,S,fH+50);

                const fln=ctx.createLinearGradient(PAD,footY,S-PAD,footY);
                fln.addColorStop(0,rgba(medal.color,0.55)); fln.addColorStop(0.5,'rgba(6,182,212,0.28)'); fln.addColorStop(1,rgba('#10b981',0.45));
                ctx.strokeStyle=fln; ctx.lineWidth=2;
                ctx.beginPath(); ctx.moveTo(PAD,footY+2); ctx.lineTo(S-PAD,footY+2); ctx.stroke();

                ctx.font='bold 30px sans-serif'; ctx.fillStyle='#4a637d';
                ctx.fillText('Junta-te ao ranking em', PAD, footY+46);

                ctx.save();
                ctx.shadowColor='rgba(56,189,248,0.60)'; ctx.shadowBlur=18;
                ctx.font='bold 50px sans-serif';
                const urlG=ctx.createLinearGradient(PAD,0,PAD+460,0);
                urlG.addColorStop(0,'#38bdf8'); urlG.addColorStop(0.5,'#818cf8'); urlG.addColorStop(1,'#34d399');
                ctx.fillStyle=urlG; ctx.fillText('checkpraia.pt', PAD, footY+100);
                ctx.restore();

                ctx.font='24px sans-serif'; ctx.fillStyle='#1a2f46';
                ctx.textAlign='right'; ctx.fillText('🌊 Bandeiras em Tempo Real', S-PAD, footY+76); ctx.textAlign='left';
            }; // end _draw

            // Load logo → draw immediately with fallback, redraw when image is ready
            const logo = window._checkpraiaLogo;
            if(logo && logo.complete && logo.naturalWidth>0){
                _draw(logo);
            } else {
                _draw(null);
                if(logo){
                    logo.onload = () => _draw(logo);
                } else {
                    const img=new Image();
                    img.onload  = () => { window._checkpraiaLogo=img; _draw(img); };
                    img.onerror = () => _draw(null);
                    img.src='/logo.png';
                    window._checkpraiaLogo=img;
                }
            }
        };

        // Share Handlers
        if (!window.shareViaAPI) window.shareViaAPI = async (data) => {
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

        document.addEventListener('livewire:navigated', () => {
            const saved = localStorage.getItem('checkpraia-theme');
            document.documentElement.setAttribute('data-theme', saved || 'dark');
        });
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
        <button @click="dismiss()" class="absolute top-3 right-3 text-slate-400 hover:text-slate-200 transition-colors p-2 touch-target" aria-label="Fechar">
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

    <!-- Cookie Consent Banner (GDPR) -->
    @if(config('ads.publisher_id'))
    <div x-data="cookieConsent()" x-init="init()" x-show="visible" x-cloak
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-4 left-4 right-4 md:bottom-6 md:left-6 md:right-auto md:max-w-md z-[70]">
        <div class="glass-card rounded-2xl border border-theme-subtle/60 shadow-2xl p-4 sm:p-5">
            <div class="flex items-start gap-3">
                <div class="shrink-0 w-8 h-8 rounded-full bg-blue-500/10 flex items-center justify-center mt-0.5">
                    <svg class="w-4 h-4 text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-theme mb-1">{{ __('common.cookie_title') }}</p>
                    <p class="text-xs text-theme-secondary leading-relaxed">{{ __('common.cookie_description') }}</p>
                    <div class="flex items-center gap-2 mt-3">
                        <button @click="accept()" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl transition-all active:scale-95 touch-target">
                            {{ __('common.cookie_accept') }}
                        </button>
                        <button @click="reject()" class="px-4 py-2 text-xs text-theme-muted hover:text-theme transition-colors font-medium touch-target">
                            {{ __('common.cookie_reject') }}
                        </button>
                        <a href="{{ route('privacy') }}" wire:navigate class="text-[10px] text-blue-400 hover:text-blue-300 underline ml-auto">{{ __('common.cookie_learn_more') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function cookieConsent() {
            return {
                visible: false,
                init() {
                    const consent = localStorage.getItem('cookie_consent');
                    if (!consent) this.visible = true;
                },
                accept() {
                    localStorage.setItem('cookie_consent', 'accepted');
                    this.visible = false;
                    if (typeof gtag === 'function') gtag('consent', 'update', { ad_storage: 'granted', analytics_storage: 'granted' });
                },
                reject() {
                    localStorage.setItem('cookie_consent', 'rejected');
                    this.visible = false;
                    if (typeof gtag === 'function') gtag('consent', 'update', { ad_storage: 'denied', analytics_storage: 'denied' });
                }
            }
        }
    </script>
    @endif
</body>
</html>
