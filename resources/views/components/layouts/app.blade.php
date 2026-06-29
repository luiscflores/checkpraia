<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

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
            background: radial-gradient(120% 120% at 50% 10%, #0d1527 0%, #070a13 100%);
            color: #f1f5f9;
        }
        .glass-card {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }
        .glass-input {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #fff;
            transition: all 0.2s ease-in-out;
        }
        .glass-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #070a13;
        }
        ::-webkit-scrollbar-thumb {
            background: #1e293b;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #334155;
        }
    </style>

    <!-- Tailwind build -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-900 text-slate-100 antialiased min-h-screen flex flex-col selection:bg-blue-600 selection:text-white">

    <!-- Responsive Container -->
    <div class="w-full flex-1 flex flex-col relative bg-slate-950/20">
        
        <!-- Header -->
        <header class="sticky top-0 z-50 bg-slate-950/80 backdrop-blur-md border-b border-white/5 transition-all duration-300">
            <div class="w-full max-w-7xl mx-auto px-4 md:px-6 py-3.5 flex items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-1.5 group">
                    <span class="text-lg">🌊</span>
                    <span class="text-base font-black tracking-tight text-white uppercase bg-clip-text bg-gradient-to-r from-blue-400 to-teal-300">CheckPraia</span>
                </a>

                <!-- Desktop Navigation Menu -->
                <nav class="hidden md:flex items-center gap-6">
                    <a href="{{ route('home') }}" class="text-xs font-bold uppercase tracking-wider transition-colors {{ request()->routeIs('home') ? 'text-blue-400 font-extrabold' : 'text-slate-400 hover:text-white' }}">
                        🗺️ Mapa
                    </a>
                    <a href="{{ route('rankings') }}" class="text-xs font-bold uppercase tracking-wider transition-colors {{ request()->routeIs('rankings') ? 'text-blue-400 font-extrabold' : 'text-slate-400 hover:text-white' }}">
                        🏆 Rankings
                    </a>
                    <a href="{{ route('profile') }}" class="text-xs font-bold uppercase tracking-wider transition-colors {{ request()->routeIs('profile') || request()->routeIs('account.*') ? 'text-blue-400 font-extrabold' : 'text-slate-400 hover:text-white' }}">
                        👤 Perfil
                    </a>
                    <a href="{{ route('admin.dashboard') }}" class="text-xs font-bold uppercase tracking-wider transition-colors {{ request()->routeIs('admin.*') ? 'text-teal-400 font-extrabold' : 'text-slate-400 hover:text-white' }}">
                        ⚙️ Admin
                    </a>
                </nav>

                <!-- Right Controls: Score and Auth -->
                <div class="flex items-center gap-2">
                    @auth
                        <span class="text-[10px] bg-gradient-to-r from-yellow-500 to-amber-500 text-slate-950 font-bold px-2 py-0.5 rounded-full shadow-sm">
                            🏆 {{ auth()->user()->score }}
                        </span>
                        <a href="{{ route('profile') }}" class="text-[10px] font-semibold text-slate-200 hover:text-white bg-slate-800 border border-slate-700 px-2.5 py-1 rounded-lg">
                            👤 {{ Str::limit(auth()->user()->name, 8) }}
                        </a>
                    @else
                        <a href="{{ route('profile') }}" class="text-[10px] font-semibold text-white bg-blue-600 hover:bg-blue-500 px-3 py-1 rounded-lg transition-all shadow-md">
                            Entrar
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 w-full max-w-7xl mx-auto px-4 md:px-6 py-6 pb-28 md:pb-12">
            {{ $slot }}

            <!-- Footer Area -->
            <footer class="w-full border-t border-white/5 py-6 mt-12 text-slate-500 text-[10px] space-y-4">
                <!-- Safety Disclaimer (Required Section 52) -->
                <div class="p-4 rounded-xl border border-red-500/20 bg-red-950/10 text-red-300 leading-relaxed shadow-sm">
                    <span class="font-bold text-red-400 uppercase tracking-wide block mb-1">⚠️ Aviso de Segurança:</span>
                    A bandeira apresentada pelo CheckPraia resulta de previsões automáticas ou partilhas da comunidade. Não constitui informação oficial. Verifica sempre a bandeira na praia e segue os nadadores-salvadores.
                </div>

                <div class="flex items-center justify-between border-t border-white/5 pt-3">
                    <div>&copy; {{ date('Y') }} CheckPraia</div>
                    <div class="flex gap-2">
                        <a href="#" class="hover:text-white transition-colors">Termos</a>
                        <span>&middot;</span>
                        <a href="#" class="hover:text-white transition-colors">Privacidade</a>
                    </div>
                </div>
            </footer>
        </main>

        <!-- Sticky Bottom Navigation Bar for Mobile Only (PWA Feel) -->
        <div class="fixed bottom-0 left-0 right-0 max-w-md mx-auto z-50 bg-slate-950/90 backdrop-blur-lg border-t border-white/10 flex justify-around items-center py-2.5 px-4 shadow-lg pb-safe md:hidden">
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-0.5 text-[9px] {{ request()->routeIs('home') ? 'text-blue-400 font-bold' : 'text-slate-400' }}">
                <span class="text-lg">🗺️</span>
                <span>Mapa</span>
            </a>
            <a href="{{ route('rankings') }}" class="flex flex-col items-center gap-0.5 text-[9px] {{ request()->routeIs('rankings') ? 'text-blue-400 font-bold' : 'text-slate-400' }}">
                <span class="text-lg">🏆</span>
                <span>Rankings</span>
            </a>
            <a href="{{ route('profile') }}" class="flex flex-col items-center gap-0.5 text-[9px] {{ request()->routeIs('profile') || request()->routeIs('account.*') ? 'text-blue-400 font-bold' : 'text-slate-400' }}">
                <span class="text-lg">👤</span>
                <span>Perfil</span>
            </a>
            <a href="{{ route('admin.dashboard') }}" class="flex flex-col items-center gap-0.5 text-[9px] {{ request()->routeIs('admin.*') ? 'text-teal-400 font-bold' : 'text-slate-400' }}">
                <span class="text-lg">⚙️</span>
                <span>Admin</span>
            </a>
        </div>

    </div>

    <!-- Leaflet Map Script -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('Service Worker registrado!', reg))
                    .catch(err => console.log('Erro ao registrar Service Worker', err));
            });
        }
    </script>

    @livewireScripts
</body>
</html>
