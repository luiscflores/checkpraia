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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="dark light">

    <title>@yield('title', 'CheckPraia - Bandeiras das Praias em Tempo Real')</title>
    <meta name="description" content="@yield('meta_description', 'Consulta a bandeira mais provável das praias marítimas vigiadas de Portugal antes de saíres de casa. Previsões automáticas e confirmações da comunidade.')">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Leaflet Map CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <!-- Style overrides for custom scrollbars and Map styling -->
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-base-gradient);
            color: var(--text-primary);
        }
        .glass-card {
            background: var(--bg-glass-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--bg-glass-border);
            box-shadow: var(--bg-glass-shadow);
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
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: var(--scrollbar-track);
        }
        ::-webkit-scrollbar-thumb {
            background: var(--scrollbar-thumb);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--scrollbar-thumb-hover);
        }
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
        .beach-popup .leaflet-popup-close-button {
            display: none;
        }
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
    </style>

    <!-- Tailwind build -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="antialiased min-h-screen flex flex-col selection:bg-blue-600 selection:text-white" style="background: var(--bg-base); color: var(--text-primary);">

    <!-- Responsive Container -->
    <div class="w-full flex-1 flex flex-col relative">
        
        <!-- Header -->
        <header class="sticky top-0 z-50 bg-theme-header backdrop-blur-md border-b border-theme-subtle transition-all duration-300" role="banner">
            <div class="w-full max-w-7xl mx-auto px-3 sm:px-4 md:px-6 py-3 flex items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-1.5 group" aria-label="CheckPraia Página Inicial">
                    <span class="text-lg" aria-hidden="true">🌊</span>
                    <span class="text-base font-black tracking-tight uppercase bg-clip-text bg-gradient-to-r from-blue-400 to-teal-300" style="color: var(--text-primary);">CheckPraia</span>
                </a>

                <!-- Desktop Navigation Menu -->
                <nav class="hidden md:flex items-center gap-6" aria-label="Navegação principal">
                    <a href="{{ route('home') }}" class="text-xs font-bold uppercase tracking-wider transition-colors {{ request()->routeIs('home') ? 'text-blue-400 font-extrabold' : 'text-theme-secondary hover:text-theme' }}" {{ request()->routeIs('home') ? 'aria-current="page"' : '' }}>
                        <span aria-hidden="true">🗺️</span> Mapa
                    </a>
                    <a href="{{ route('rankings') }}" class="text-xs font-bold uppercase tracking-wider transition-colors {{ request()->routeIs('rankings') ? 'text-blue-400 font-extrabold' : 'text-theme-secondary hover:text-theme' }}" {{ request()->routeIs('rankings') ? 'aria-current="page"' : '' }}>
                        <span aria-hidden="true">🏆</span> Rankings
                    </a>
                    <a href="{{ route('profile') }}" class="text-xs font-bold uppercase tracking-wider transition-colors {{ request()->routeIs('profile') || request()->routeIs('account.*') ? 'text-blue-400 font-extrabold' : 'text-theme-secondary hover:text-theme' }}" {{ request()->routeIs('profile') || request()->routeIs('account.*') ? 'aria-current="page"' : '' }}>
                        <span aria-hidden="true">👤</span> Perfil
                    </a>
                    @auth
                        @if(auth()->user()->is_admin)
                            <a href="{{ route('admin.dashboard') }}" class="text-xs font-bold uppercase tracking-wider transition-colors {{ request()->routeIs('admin.*') ? 'text-teal-400 font-extrabold' : 'text-theme-secondary hover:text-theme' }}" {{ request()->routeIs('admin.*') ? 'aria-current="page"' : '' }}>
                                <span aria-hidden="true">⚙️</span> Admin
                            </a>
                        @endif
                    @endauth
                </nav>

                <!-- Right Controls: Theme Toggle, Score and Auth -->
                <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                    <!-- Theme Toggle -->
                    <button onclick="toggleAppTheme()" class="theme-toggle-btn touch-target" aria-label="Alternar tema" title="Alternar tema">
                        <span class="theme-toggle-dark-icon" style="font-size: 18px; line-height: 1;">🌙</span>
                        <span class="theme-toggle-light-icon" style="font-size: 18px; line-height: 1;">☀️</span>
                    </button>
                    @auth
                        <span class="text-xs sm:text-sm bg-gradient-to-r from-yellow-500 to-amber-500 text-slate-950 font-bold px-1.5 sm:px-2.5 py-0.5 rounded-full shadow-sm whitespace-nowrap" aria-label="{{ auth()->user()->score }} pontos">
                            <span aria-hidden="true">🏆</span> {{ auth()->user()->score }}
                        </span>
                        <a href="{{ route('profile') }}" class="text-xs sm:text-sm font-semibold text-theme bg-theme-card border border-theme-medium px-2 sm:px-2.5 py-1.5 rounded-lg truncate max-w-[80px] sm:max-w-none" aria-label="Área pessoal de {{ auth()->user()->name }}">
                            <span aria-hidden="true">👤</span> {{ Str::limit(auth()->user()->name, 5, '') }}
                        </a>
                    @else
                        <a href="{{ route('profile') }}" class="text-xs sm:text-sm font-semibold text-white bg-blue-600 hover:bg-blue-500 px-3.5 py-1.5 sm:py-1.5 rounded-lg transition-all shadow-md touch-target inline-flex items-center">
                            Entrar
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
                <!-- Safety Disclaimer (Required Section 52) -->
                <div class="p-4 rounded-xl border border-red-500/20 bg-red-950/10 text-red-300 leading-relaxed shadow-sm" role="alert">
                    <span class="font-bold text-red-400 uppercase tracking-wide block mb-1"><span aria-hidden="true">⚠️</span> Aviso de Segurança:</span>
                    A bandeira apresentada pelo CheckPraia resulta de previsões automáticas ou partilhas da comunidade. Não constitui informação oficial. Verifica sempre a bandeira na praia e segue os nadadores-salvadores.
                </div>

                <div class="flex items-center justify-between border-t border-theme-subtle pt-3">
                    <div>&copy; {{ date('Y') }} CheckPraia</div>
                    <div class="flex gap-2">
                        <a href="#" class="hover:text-theme transition-colors">Termos</a>
                        <span aria-hidden="true">&middot;</span>
                        <a href="#" class="hover:text-theme transition-colors">Privacidade</a>
                    </div>
                </div>
            </footer>
        </main>

        <!-- Sticky Bottom Navigation Bar for Mobile Only (PWA Feel) -->
        <nav class="fixed bottom-0 left-0 right-0 z-50 bg-theme-nav backdrop-blur-lg border-t border-theme-medium flex justify-around items-center py-1.5 px-4 pb-safe md:hidden" aria-label="Navegação móvel">
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-0.5 text-xs min-w-[60px] py-1 {{ request()->routeIs('home') ? 'text-blue-400 font-bold' : 'text-theme-secondary' }}" {{ request()->routeIs('home') ? 'aria-current="page"' : '' }}>
                <span class="text-xl leading-none mb-0.5" aria-hidden="true">🗺️</span>
                <span>Mapa</span>
            </a>
            <a href="{{ route('rankings') }}" class="flex flex-col items-center gap-0.5 text-xs min-w-[60px] py-1 {{ request()->routeIs('rankings') ? 'text-blue-400 font-bold' : 'text-theme-secondary' }}" {{ request()->routeIs('rankings') ? 'aria-current="page"' : '' }}>
                <span class="text-xl leading-none mb-0.5" aria-hidden="true">🏆</span>
                <span>Rankings</span>
            </a>
            <a href="{{ route('profile') }}" class="flex flex-col items-center gap-0.5 text-xs min-w-[60px] py-1 {{ request()->routeIs('profile') || request()->routeIs('account.*') ? 'text-blue-400 font-bold' : 'text-theme-secondary' }}" {{ request()->routeIs('profile') || request()->routeIs('account.*') ? 'aria-current="page"' : '' }}>
                <span class="text-xl leading-none mb-0.5" aria-hidden="true">👤</span>
                <span>Perfil</span>
            </a>
            @auth
                @if(auth()->user()->is_admin)
                    <a href="{{ route('admin.dashboard') }}" class="flex flex-col items-center gap-0.5 text-xs min-w-[60px] py-1 {{ request()->routeIs('admin.*') ? 'text-teal-400 font-bold' : 'text-theme-secondary' }}" {{ request()->routeIs('admin.*') ? 'aria-current="page"' : '' }}>
                        <span class="text-xl leading-none mb-0.5" aria-hidden="true">⚙️</span>
                        <span>Admin</span>
                    </a>
                @endif
            @endauth
        </nav>

    </div>

    <!-- Leaflet Map Script -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
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
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('Service Worker registrado!', reg))
                    .catch(err => console.log('Erro ao registrar Service Worker', err));
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
