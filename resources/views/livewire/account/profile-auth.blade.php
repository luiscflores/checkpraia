<div class="max-w-md mx-auto my-12">
    @section('title', 'Autenticação - CheckPraia')

    <div class="glass-card p-5 sm:p-8 rounded-3xl border border-theme-medium space-y-6 relative overflow-hidden">
        <!-- Accent light -->
        <div class="absolute w-32 h-32 rounded-full blur-3xl opacity-20 -top-12 -right-12 bg-blue-500"></div>

        <!-- Heading tabs -->
        <div class="flex border-b border-theme-medium text-center font-bold text-sm">
            <button 
                @click="$wire.set('isRegister', false)" 
                class="flex-1 pb-3 border-b-2 transition-all {{ !$isRegister ? 'border-blue-500 text-theme' : 'border-transparent text-theme-secondary hover:text-theme' }}">
                Entrar
            </button>
            <button 
                @click="$wire.set('isRegister', true)" 
                class="flex-1 pb-3 border-b-2 transition-all {{ $isRegister ? 'border-blue-500 text-theme' : 'border-transparent text-theme-secondary hover:text-theme' }}">
                Registar
            </button>
        </div>

        @if (session()->has('auth_success'))
            <div class="p-3 bg-emerald-500/20 border border-emerald-500/30 text-emerald-200 text-xs rounded-xl font-medium">
                ✔️ {{ session('auth_success') }}
            </div>
        @endif

        <!-- Login Form -->
        @if (!$isRegister)
            <form wire:submit.prevent="login" class="space-y-4">
                @error('login')
                    <div class="p-3 bg-rose-500/20 border border-rose-500/30 text-rose-200 text-xs rounded-xl font-medium">
                        ❌ {{ $message }}
                    </div>
                @enderror

                <div class="space-y-1">
                    <label class="text-xs text-theme-secondary font-bold uppercase tracking-wider block">Endereço de Email</label>
                    <input type="email" wire:model="loginEmail" class="w-full glass-input px-4 py-2.5 rounded-xl text-base sm:text-sm" placeholder="exemplo@email.com" />
                    @error('loginEmail') <span class="text-xs text-rose-400 block">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-xs text-theme-secondary font-bold uppercase tracking-wider block">Palavra-passe</label>
                    <input type="password" wire:model="loginPassword" class="w-full glass-input px-4 py-2.5 rounded-xl text-base sm:text-sm" placeholder="******" />
                    @error('loginPassword') <span class="text-xs text-rose-400 block">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-xl text-sm transition-all shadow-md shadow-blue-500/20">
                    Entrar na Conta &rarr;
                </button>

                <div class="relative my-4">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-white/10"></div>
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="bg-[#0b1221] px-2 text-slate-400">ou</span>
                    </div>
                </div>

                <a href="{{ route('auth.google') }}" class="w-full flex items-center justify-center gap-3 bg-white hover:bg-gray-100 text-gray-800 font-bold py-3 rounded-xl text-sm transition-all shadow-md">
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Entrar com Google
                </a>
            </form>
        @else
            <!-- Registration Form -->
            <form wire:submit.prevent="register" class="space-y-4">
                <div class="space-y-1">
                    <label class="text-xs text-theme-secondary font-bold uppercase tracking-wider block">Nome Completo</label>
                    <input type="text" wire:model="name" class="w-full glass-input px-4 py-2.5 rounded-xl text-base sm:text-sm" placeholder="Ex: Luis Flores" />
                    @error('name') <span class="text-xs text-rose-400 block">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-xs text-theme-secondary font-bold uppercase tracking-wider block">Nome de Utilizador (Público)</label>
                    <input type="text" wire:model="username" class="w-full glass-input px-4 py-2.5 rounded-xl text-base sm:text-sm" placeholder="Ex: luisflores" />
                    @error('username') <span class="text-xs text-rose-400 block">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-xs text-theme-secondary font-bold uppercase tracking-wider block">Endereço de Email</label>
                    <input type="email" wire:model="email" class="w-full glass-input px-4 py-2.5 rounded-xl text-base sm:text-sm" placeholder="exemplo@email.com" />
                    @error('email') <span class="text-xs text-rose-400 block">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-xs text-theme-secondary font-bold uppercase tracking-wider block">Palavra-passe</label>
                    <input type="password" wire:model="password" class="w-full glass-input px-4 py-2.5 rounded-xl text-base sm:text-sm" placeholder="Mínimo 6 caracteres" />
                    @error('password') <span class="text-xs text-rose-400 block">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-xs text-theme-secondary font-bold uppercase tracking-wider block">Confirmar Palavra-passe</label>
                    <input type="password" wire:model="password_confirmation" class="w-full glass-input px-4 py-2.5 rounded-xl text-base sm:text-sm" placeholder="Repete a palavra-passe" />
                </div>

                <div class="space-y-1">
                    <label class="text-xs text-theme-secondary font-bold uppercase tracking-wider block">Código de Convite (Opcional)</label>
                    <input type="text" wire:model="referralCodeInput" class="w-full glass-input px-4 py-2.5 rounded-xl text-base sm:text-sm" placeholder="Ex: ABC123XYZ" />
                    @error('referralCodeInput') <span class="text-xs text-rose-400 block">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-xl text-sm transition-all shadow-md shadow-blue-500/20">
                    Registar Nova Conta &rarr;
                </button>
            </form>
        @endif
    </div>
</div>