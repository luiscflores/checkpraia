<div class="max-w-md mx-auto my-12">
    @section('title', 'Autenticação - CheckPraia')

    <div class="glass-card p-8 rounded-3xl border border-white/10 space-y-6 relative overflow-hidden">
        <!-- Accent light -->
        <div class="absolute w-32 h-32 rounded-full blur-3xl opacity-20 -top-12 -right-12 bg-blue-500"></div>

        <!-- Heading tabs -->
        <div class="flex border-b border-white/10 text-center font-bold text-sm">
            <button 
                @click="$wire.set('isRegister', false)" 
                class="flex-1 pb-3 border-b-2 transition-all {{ !$isRegister ? 'border-blue-500 text-white' : 'border-transparent text-slate-400 hover:text-white' }}">
                Entrar
            </button>
            <button 
                @click="$wire.set('isRegister', true)" 
                class="flex-1 pb-3 border-b-2 transition-all {{ $isRegister ? 'border-blue-500 text-white' : 'border-transparent text-slate-400 hover:text-white' }}">
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
                    <label class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Endereço de Email</label>
                    <input type="email" wire:model="loginEmail" class="w-full glass-input px-4 py-2.5 rounded-xl text-sm" placeholder="exemplo@email.com" />
                    @error('loginEmail') <span class="text-[10px] text-rose-400 block">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Palavra-passe</label>
                    <input type="password" wire:model="loginPassword" class="w-full glass-input px-4 py-2.5 rounded-xl text-sm" placeholder="******" />
                    @error('loginPassword') <span class="text-[10px] text-rose-400 block">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-xl text-sm transition-all shadow-md shadow-blue-500/20">
                    Entrar na Conta &rarr;
                </button>
            </form>
        @else
            <!-- Registration Form -->
            <form wire:submit.prevent="register" class="space-y-4">
                <div class="space-y-1">
                    <label class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Nome Completo</label>
                    <input type="text" wire:model="name" class="w-full glass-input px-4 py-2.5 rounded-xl text-sm" placeholder="Ex: Luis Flores" />
                    @error('name') <span class="text-[10px] text-rose-400 block">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Nome de Utilizador (Público)</label>
                    <input type="text" wire:model="username" class="w-full glass-input px-4 py-2.5 rounded-xl text-sm" placeholder="Ex: luisflores" />
                    @error('username') <span class="text-[10px] text-rose-400 block">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Endereço de Email</label>
                    <input type="email" wire:model="email" class="w-full glass-input px-4 py-2.5 rounded-xl text-sm" placeholder="exemplo@email.com" />
                    @error('email') <span class="text-[10px] text-rose-400 block">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Palavra-passe</label>
                    <input type="password" wire:model="password" class="w-full glass-input px-4 py-2.5 rounded-xl text-sm" placeholder="Mínimo 6 caracteres" />
                    @error('password') <span class="text-[10px] text-rose-400 block">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Código de Convite (Opcional)</label>
                    <input type="text" wire:model="referralCodeInput" class="w-full glass-input px-4 py-2.5 rounded-xl text-sm" placeholder="Ex: ABC123XYZ" />
                    @error('referralCodeInput') <span class="text-[10px] text-rose-400 block">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-xl text-sm transition-all shadow-md shadow-blue-500/20">
                    Registar Nova Conta &rarr;
                </button>
            </form>
        @endif
    </div>
</div>
