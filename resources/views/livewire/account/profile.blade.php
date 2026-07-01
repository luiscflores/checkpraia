<div class="space-y-8">
    @section('title', 'Área Pessoal - CheckPraia')

    @if (session()->has('auth_success'))
        <div class="p-3 bg-emerald-500/20 border border-emerald-500/30 text-emerald-200 text-xs rounded-xl font-medium">
            ✔️ {{ session('auth_success') }}
        </div>
    @endif

    <!-- Stats Header Dashboard Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
        <!-- Score Card -->
        <div class="glass-card p-4 sm:p-6 rounded-2xl border border-theme-medium flex items-center justify-between">
            <div>
                <span class="text-xs text-theme-secondary uppercase font-bold tracking-wider block">Pontuação Total</span>
                <span class="text-3xl font-black text-theme block mt-1">{{ auth()->user()->score }} <span class="text-xs text-theme-muted font-normal">pts</span></span>
            </div>
            <span class="text-3xl">🏆</span>
        </div>

        <!-- Approved Card -->
        <div class="glass-card p-4 sm:p-6 rounded-2xl border border-theme-medium flex items-center justify-between">
            <div>
                <span class="text-xs text-theme-secondary uppercase font-bold tracking-wider block">Confirmados Aceites</span>
                <span class="text-3xl font-black text-emerald-400 block mt-1">{{ auth()->user()->accepted_confirmations_count }}</span>
            </div>
            <span class="text-3xl text-emerald-400">✔️</span>
        </div>

        <!-- Penalized Card -->
        <div class="glass-card p-4 sm:p-6 rounded-2xl border border-theme-medium flex items-center justify-between">
            <div>
                <span class="text-xs text-theme-secondary uppercase font-bold tracking-wider block">Confirmados Penalizados</span>
                <span class="text-3xl font-black text-rose-400 block mt-1">{{ auth()->user()->penalized_confirmations_count }}</span>
            </div>
            <span class="text-3xl text-rose-400">❌</span>
        </div>

        <!-- Referrals Card -->
        <div class="glass-card p-4 sm:p-6 rounded-2xl border border-theme-medium flex items-center justify-between">
            <div>
                <span class="text-xs text-theme-secondary uppercase font-bold tracking-wider block">Amigos Convidados</span>
                <span class="text-3xl font-black text-blue-400 block mt-1">{{ $totalQualifiedInvited }} <span class="text-xs text-theme-muted font-normal">/ {{ $totalInvited }}</span></span>
            </div>
            <span class="text-3xl text-blue-400">👥</span>
        </div>
    </div>

    <!-- Main Dashboard Body Split -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Left Column: Settings and Invite Program (Span 5) -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Edit Profile details -->
            <div class="glass-card p-6 rounded-3xl space-y-4">
                <h3 class="text-lg font-bold text-theme">Definições do Perfil</h3>
                
                @if(session()->has('profile_success'))
                    <div class="p-3 bg-emerald-500/20 border border-emerald-500/30 text-emerald-200 text-xs rounded-xl font-medium">
                        ✔️ {{ session('profile_success') }}
                    </div>
                @endif

                <form wire:submit.prevent="updateProfile" class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-xs text-theme-secondary font-bold block">Nome Completo</label>
                        <input type="text" wire:model="editName" class="w-full glass-input px-3.5 py-2.5 rounded-xl text-base sm:text-sm" />
                        @error('editName') <span class="text-xs text-rose-400 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs text-theme-secondary font-bold block">Nome de Utilizador</label>
                        <input type="text" wire:model="editUsername" class="w-full glass-input px-3.5 py-2.5 rounded-xl text-base sm:text-sm" />
                        @error('editUsername') <span class="text-xs text-rose-400 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-500 active:scale-[0.98] text-white font-bold py-3 sm:py-2.5 rounded-xl text-sm transition-all touch-target">
                            Guardar Alterações
                        </button>
                        <button type="button" wire:click="logout" class="bg-theme-card active:scale-[0.98] text-theme-secondary font-semibold px-5 sm:px-4 py-3 sm:py-2.5 rounded-xl text-sm border border-theme-medium transition-all touch-target hover:text-theme">
                            Sair
                        </button>
                    </div>
                </form>
            </div>

            <!-- Referrals Card -->
            <div class="glass-card p-6 rounded-3xl space-y-4 relative overflow-hidden">
                <h3 class="text-lg font-bold text-theme flex items-center gap-2">
                    👥 Programa de Convites
                </h3>
                <p class="text-xs text-theme-secondary leading-relaxed">
                    Partilha o teu código. A cada 5 amigos convidados que efetuem uma confirmação válida, ganhas <span class="text-theme font-bold">+10 pontos</span>!
                </p>

                <div class="bg-theme-card p-4 rounded-2xl border border-theme-subtle space-y-2">
                    <span class="text-xs text-theme-secondary font-bold block uppercase tracking-wider">O teu código de convite:</span>
                    <div class="flex items-center justify-between bg-theme-card px-3.5 py-2 rounded-xl border border-theme-medium">
                        <span class="font-mono text-base font-black tracking-widest text-theme select-all">{{ auth()->user()->referral_code }}</span>
                        <button onclick="navigator.clipboard.writeText('{{ auth()->user()->referral_code }}'); alert('Código copiado!');" class="text-xs text-blue-400 hover:underline">Copiar</button>
                    </div>
                </div>

                <!-- Referral progress tracker -->
                <div class="space-y-1.5 pt-2">
                    <div class="flex justify-between text-xs font-semibold">
                        <span class="text-theme-secondary">Progresso para o próximo bónus</span>
                        <span class="text-theme">{{ $referralProgress }} / 5 convites</span>
                    </div>
                    <div class="w-full bg-theme-card h-2.5 rounded-full overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-500 to-teal-400 h-full transition-all duration-500" style="width: {{ ($referralProgress / 5) * 100 }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="glass-card p-6 rounded-3xl space-y-3 border-rose-500/20">
                <h3 class="text-sm font-bold text-rose-400 uppercase tracking-wider">Zona de Risco</h3>
                <p class="text-xs text-theme-muted leading-relaxed">A exclusão da tua conta é permanente. Todos os teus pontos de score e histórico de relatórios de GPS serão apagados do sistema.</p>
                <button onclick="if(confirm('Tem a certeza de que deseja eliminar permanentemente a sua conta? Todos os dados serão perdidos.')) { @this.call('deleteAccount') }" class="w-full bg-rose-500/10 hover:bg-rose-500 active:scale-[0.98] text-rose-400 hover:text-white border border-rose-500/20 hover:border-rose-500 font-bold py-3 sm:py-2.5 rounded-xl text-sm transition-all touch-target">
                    Eliminar Conta Permanentemente
                </button>
            </div>
        </div>

        <!-- Right Column: Favorites and Logs (Span 7) -->
        <div class="lg:col-span-7 space-y-6">
            
            <!-- Favorites list -->
            <div class="glass-card p-6 rounded-3xl space-y-4">
                <h3 class="text-lg font-bold text-theme flex items-center gap-2">
                    ⭐ Praias Favoritas
                </h3>
                
                @if(session()->has('favorite_removed'))
                    <div class="p-2 bg-emerald-500/20 border border-emerald-500/30 text-emerald-200 text-xs rounded-xl font-medium">
                        {{ session('favorite_removed') }}
                    </div>
                @endif

                <div class="space-y-2">
                    @forelse($favorites as $favorite)
                        <div class="bg-theme-card border border-theme-subtle p-3 rounded-2xl flex items-center justify-between">
                            <div>
                                <a href="{{ $favorite->url }}" class="font-bold text-theme hover:text-blue-400 transition-colors text-sm">{{ $favorite->name }}</a>
                                <p class="text-xs text-theme-muted">📍 {{ $favorite->municipality }}</p>
                            </div>

                            <div class="flex items-center gap-3">
                                @php
                                    $favFlag = $favorite->currentStatus ? $favorite->currentStatus->flag : 'gray';
                                    $favColor = match($favFlag) {
                                        'green' => 'bg-emerald-500 text-slate-950',
                                        'yellow' => 'bg-amber-500 text-slate-950',
                                        'red' => 'bg-rose-500 text-white',
                                        'blue_or_neutral' => 'bg-blue-600 text-white',
                                        default => 'bg-slate-600 text-slate-300'
                                    };
                                    $favFlagName = match($favFlag) {
                                        'green' => 'Verde',
                                        'yellow' => 'Amarela',
                                        'red' => 'Vermelha',
                                        'blue_or_neutral' => 'Fora de Época',
                                        default => '🔘'
                                    };
                                @endphp
                                <span class="px-2.5 py-1 text-xs font-extrabold rounded-full {{ $favColor }}">
                                    {{ $favFlagName }}
                                </span>
                                
                                <button wire:click="removeFavorite({{ $favorite->id }})" class="text-theme-muted hover:text-rose-400 text-sm transition-colors" title="Remover dos favoritos">
                                    🗑️
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-theme-muted text-center py-4">Ainda não guardaste nenhuma praia nos favoritos.</p>
                    @endforelse
                </div>
            </div>

            <!-- Confirmations history -->
            <div class="glass-card p-6 rounded-3xl space-y-4">
                <h3 class="text-lg font-bold text-theme flex items-center gap-2">
                    📋 Histórico de Confirmações (Visitas)
                </h3>

                <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                    @forelse($reports as $report)
                        <div class="bg-theme-card border border-theme-subtle p-3 rounded-2xl flex items-center justify-between text-xs">
                            <div class="space-y-1">
                                <span class="font-bold text-theme block">{{ $report->beach->name }}</span>
                                <span class="text-xs text-theme-muted block">{{ $report->reported_at->format('d/m/Y H:i') }} &bull; GPS: {{ round($report->distance_to_beach, 2) }} km</span>
                            </div>

                            <div class="flex items-center gap-3">
                                @php
                                    $histColor = match($report->flag) {
                                        'green' => 'text-emerald-400',
                                        'yellow' => 'text-amber-400',
                                        default => 'text-rose-400'
                                    };
                                    $statusBadge = match($report->status) {
                                        'confirmed' => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
                                        'rejected' => 'bg-rose-500/10 text-rose-400 border border-rose-500/20',
                                        'cancelled' => 'bg-theme-card text-theme-muted border border-theme-medium',
                                        default => 'bg-blue-500/10 text-blue-400 border border-blue-500/20'
                                    };
                                    $statusName = match($report->status) {
                                        'confirmed' => 'Aceite',
                                        'rejected' => 'Penalizada',
                                        'cancelled' => 'Cancelada',
                                        default => 'Pendente'
                                    };
                                @endphp
                                <span class="font-bold {{ $histColor }} uppercase tracking-wider text-xs">
                                    Bandeira {{ match($report->flag) { 'green' => 'Verde', 'yellow' => 'Amarela', default => 'Vermelha' } }}
                                </span>

                                <span class="px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wider {{ $statusBadge }}">
                                    {{ $statusName }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-theme-muted text-center py-4">Ainda não enviaste nenhuma confirmação.</p>
                    @endforelse
                </div>
            </div>

            <!-- Score Transactions ledger -->
            <div class="glass-card p-6 rounded-3xl space-y-4">
                <h3 class="text-lg font-bold text-theme flex items-center gap-2">
                    💰 Extrato de Pontos
                </h3>

                <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                    @forelse($transactions as $tx)
                        <div class="bg-theme-card border border-theme-subtle p-3 rounded-2xl flex items-center justify-between text-xs">
                            <div class="space-y-1">
                                <span class="font-bold text-theme block">{{ $tx->description }}</span>
                                <span class="text-xs text-theme-muted block">{{ $tx->created_at->format('d/m/Y H:i') }}</span>
                            </div>

                            <span class="font-black text-sm {{ $tx->points > 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                {{ $tx->points > 0 ? '+' : '' }}{{ $tx->points }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-theme-muted text-center py-4">Sem transações de pontos registadas.</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>
</div>