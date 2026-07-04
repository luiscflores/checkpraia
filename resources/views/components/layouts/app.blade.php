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
    <link rel="apple-touch-icon" href="/icon-192.png">
    <meta name="mobile-web-app-capable" content="yes">

    <title>@yield('title', __('common.site_name') . ' - ' . __('common.site_description'))</title>
    <meta name="description" content="@yield('meta_description', __('common.meta_description'))">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Preconnect for map tiles -->
    <link rel="preconnect" href="https://unpkg.com">
    <link rel="dns-prefetch" href="https://unpkg.com">
    <link rel="preconnect" href="https://server.arcgisonline.com">
    <link rel="dns-prefetch" href="https://tile.openstreetmap.org">

    <!-- Leaflet Map CSS (non-blocking) -->
    <link rel="preload" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"></noscript>

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

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="antialiased min-h-screen flex flex-col selection:bg-blue-600 selection:text-white" style="background: var(--bg-base); color: var(--text-primary);">

    <!-- Skip to content -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[100] focus:px-4 focus:py-2 focus:bg-blue-600 focus:text-white focus:rounded-xl focus:font-bold focus:text-sm focus:outline-none">
        Saltar para o conteúdo
    </a>

    <div class="w-full flex-1 flex flex-col relative">

        <!-- Header -->
        <header class="sticky top-0 z-50 bg-theme-header backdrop-blur-md border-b border-theme-subtle transition-all duration-300" role="banner">
            <div class="w-full max-w-7xl mx-auto px-3 sm:px-4 md:px-6 py-3 flex items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-1.5 group" aria-label="{{ __('common.nav_home') }}">
                    <span class="text-lg transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-12" aria-hidden="true">🌊</span>
                    <span class="text-base font-black tracking-tight uppercase bg-clip-text bg-gradient-to-r from-blue-400 to-teal-300" style="color: var(--text-primary);">{{ __('common.site_name') }}</span>
                </a>

                <!-- Desktop Navigation Menu -->
                <nav class="hidden md:flex items-center gap-4 lg:gap-6" aria-label="{{ __('common.nav_map') }}">
                    <a href="{{ route('home') }}" class="relative text-xs font-bold uppercase tracking-wider transition-all duration-200 {{ request()->routeIs('home*') ? 'text-blue-400 font-extrabold' : 'text-theme-secondary hover:text-theme hover:scale-105' }}" {{ request()->routeIs('home*') ? 'aria-current="page"' : '' }}>
                        <span aria-hidden="true">🗺️</span> {{ __('common.nav_map') }}
                        @if(request()->routeIs('home*'))
                            <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-5 h-0.5 bg-blue-400 rounded-full"></span>
                        @endif
                    </a>
                    <a href="{{ route('rankings') }}" class="relative text-xs font-bold uppercase tracking-wider transition-all duration-200 {{ request()->routeIs('rankings*') ? 'text-blue-400 font-extrabold' : 'text-theme-secondary hover:text-theme hover:scale-105' }}" {{ request()->routeIs('rankings*') ? 'aria-current="page"' : '' }}>
                        <span aria-hidden="true">🏆</span> {{ __('common.nav_rankings') }}
                        @if(request()->routeIs('rankings*'))
                            <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-5 h-0.5 bg-blue-400 rounded-full"></span>
                        @endif
                    </a>
                    <a href="{{ route('profile') }}" class="relative text-xs font-bold uppercase tracking-wider transition-all duration-200 {{ request()->routeIs('profile*') || request()->routeIs('account.*') ? 'text-blue-400 font-extrabold' : 'text-theme-secondary hover:text-theme hover:scale-105' }}" {{ request()->routeIs('profile*') || request()->routeIs('account.*') ? 'aria-current="page"' : '' }}>
                        <span aria-hidden="true">👤</span> {{ __('common.nav_profile') }}
                        @if(request()->routeIs('profile*') || request()->routeIs('account.*'))
                            <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-5 h-0.5 bg-blue-400 rounded-full"></span>
                        @endif
                    </a>
                    @auth
                        @if(auth()->user()->is_admin)
                            <a href="{{ route('admin.dashboard') }}" class="relative text-xs font-bold uppercase tracking-wider transition-all duration-200 {{ request()->routeIs('admin.*') ? 'text-teal-400 font-extrabold' : 'text-theme-secondary hover:text-theme hover:scale-105' }}" {{ request()->routeIs('admin.*') ? 'aria-current="page"' : '' }}>
                                <span aria-hidden="true">⚙️</span> {{ __('common.nav_admin') }}
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
                <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
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

                    <!-- Theme Toggle -->
                    <button onclick="toggleAppTheme()" class="theme-toggle-btn touch-target" aria-label="{{ __('common.theme_toggle') }}" title="{{ __('common.theme_toggle') }}">
                        <span class="theme-toggle-dark-icon" style="font-size: 18px; line-height: 1; transition: transform 0.3s ease;" x-on:click="$el.style.transform = 'rotate(360deg)'">🌙</span>
                        <span class="theme-toggle-light-icon" style="font-size: 18px; line-height: 1; transition: transform 0.3s ease;">☀️</span>
                    </button>
                    @auth
                        <span class="text-xs sm:text-sm bg-gradient-to-r from-yellow-500 to-amber-500 text-slate-950 font-bold px-1.5 sm:px-2.5 py-0.5 rounded-full shadow-sm whitespace-nowrap animate-scale-in" aria-label="{{ trans_choice('common.nav_score_label', auth()->user()->score, ['score' => auth()->user()->score]) }}">
                            <span aria-hidden="true">🏆</span> {{ auth()->user()->score }}
                        </span>
                        <a href="{{ route('profile') }}" class="text-xs sm:text-sm font-semibold text-theme bg-theme-card border border-theme-medium px-2 sm:px-2.5 py-1.5 rounded-lg truncate max-w-[80px] sm:max-w-none transition-all hover:border-blue-500/40" aria-label="{{ __('common.nav_profile') }}">
                            <span aria-hidden="true">👤</span> {{ Str::limit(auth()->user()->name, 5, '') }}
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="theme-toggle-btn touch-target" title="{{ __('common.nav_logout') }}">
                                <svg class="w-[18px] h-[18px] transition-transform hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('profile') }}" class="text-xs sm:text-sm font-semibold text-white bg-blue-600 hover:bg-blue-500 px-3.5 py-1.5 sm:py-1.5 rounded-lg transition-all shadow-md touch-target inline-flex items-center hover:shadow-lg hover:shadow-blue-500/25 active:scale-95">
                            {{ __('common.nav_login') }}
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main id="main-content" class="flex-1 w-full max-w-7xl mx-auto px-3 sm:px-4 md:px-6 py-5 sm:py-6 pb-28 md:pb-12" role="main">
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
                        <a href="#" class="hover:text-theme transition-colors">{{ __('common.footer_terms') }}</a>
                        <span aria-hidden="true">&middot;</span>
                        <a href="#" class="hover:text-theme transition-colors">{{ __('common.footer_privacy') }}</a>
                    </div>
                </div>
            </footer>
        </main>

        <!-- Sticky Bottom Navigation Bar for Mobile Only (PWA Feel) -->
        <nav class="fixed bottom-0 left-0 right-0 z-50 bg-theme-nav backdrop-blur-lg border-t border-theme-medium flex justify-around items-center py-1.5 px-4 pb-safe md:hidden shadow-[0_-4px_20px_rgba(0,0,0,0.15)]" aria-label="{{ __('common.nav_mobile_map') }}">
            <a href="{{ route('home') }}" class="relative flex flex-col items-center gap-0.5 text-xs min-w-[60px] py-1 transition-all duration-200 {{ request()->routeIs('home*') ? 'text-blue-400 font-bold scale-105' : 'text-theme-secondary hover:text-theme' }}" {{ request()->routeIs('home*') ? 'aria-current="page"' : '' }}>
                <span class="text-xl leading-none mb-0.5 transition-transform duration-200" aria-hidden="true">🗺️</span>
                <span>{{ __('common.nav_mobile_map') }}</span>
                @if(request()->routeIs('home*'))
                    <span class="absolute -top-0.5 left-1/2 -translate-x-1/2 w-8 h-0.5 bg-blue-400 rounded-full"></span>
                @endif
            </a>
            <a href="{{ route('rankings') }}" class="relative flex flex-col items-center gap-0.5 text-xs min-w-[60px] py-1 transition-all duration-200 {{ request()->routeIs('rankings*') ? 'text-blue-400 font-bold scale-105' : 'text-theme-secondary hover:text-theme' }}" {{ request()->routeIs('rankings*') ? 'aria-current="page"' : '' }}>
                <span class="text-xl leading-none mb-0.5 transition-transform duration-200" aria-hidden="true">🏆</span>
                <span>{{ __('common.nav_mobile_rankings') }}</span>
                @if(request()->routeIs('rankings*'))
                    <span class="absolute -top-0.5 left-1/2 -translate-x-1/2 w-8 h-0.5 bg-blue-400 rounded-full"></span>
                @endif
            </a>
            <a href="{{ route('profile') }}" class="relative flex flex-col items-center gap-0.5 text-xs min-w-[60px] py-1 transition-all duration-200 {{ request()->routeIs('profile*') || request()->routeIs('account.*') ? 'text-blue-400 font-bold scale-105' : 'text-theme-secondary hover:text-theme' }}" {{ request()->routeIs('profile*') || request()->routeIs('account.*') ? 'aria-current="page"' : '' }}>
                <span class="text-xl leading-none mb-0.5 transition-transform duration-200" aria-hidden="true">👤</span>
                <span>{{ __('common.nav_mobile_profile') }}</span>
                @if(request()->routeIs('profile*') || request()->routeIs('account.*'))
                    <span class="absolute -top-0.5 left-1/2 -translate-x-1/2 w-8 h-0.5 bg-blue-400 rounded-full"></span>
                @endif
            </a>
            @auth
                @if(auth()->user()->is_admin)
                    <a href="{{ route('admin.dashboard') }}" class="relative flex flex-col items-center gap-0.5 text-xs min-w-[60px] py-1 transition-all duration-200 {{ request()->routeIs('admin.*') ? 'text-teal-400 font-bold scale-105' : 'text-theme-secondary hover:text-theme' }}" {{ request()->routeIs('admin.*') ? 'aria-current="page"' : '' }}>
                        <span class="text-xl leading-none mb-0.5 transition-transform duration-200" aria-hidden="true">⚙️</span>
                        <span>{{ __('common.nav_mobile_admin') }}</span>
                        @if(request()->routeIs('admin.*'))
                            <span class="absolute -top-0.5 left-1/2 -translate-x-1/2 w-8 h-0.5 bg-teal-400 rounded-full"></span>
                        @endif
                    </a>
                @endif
            @endauth
        </nav>
    </div>

    <!-- Leaflet Map Script (deferred) -->
    <script defer src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

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
        });

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
</body>
</html>
