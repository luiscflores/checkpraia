<div class="space-y-8">
    @section('title', 'Painel de Administração - CheckPraia')

    <div class="glass-card p-6 rounded-2xl border border-theme-medium flex justify-between items-center bg-teal-950/10 border-teal-500/20">
        <div>
            <h1 class="text-2xl font-bold text-teal-300 tracking-tight">Painel de Backoffice</h1>
            <p class="text-sm text-theme-secondary">Monitoriza as métricas do CheckPraia, gere utilizadores e agenda campanhas.</p>
        </div>
        <span class="text-sm font-mono bg-theme-card border border-theme-medium px-3 py-1.5 rounded-lg text-theme-secondary">Acesso Administrativo</span>
    </div>

    <!-- Backoffice Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="glass-card p-4 rounded-xl text-center">
            <span class="text-xs text-theme-secondary uppercase font-bold tracking-wider block">Utilizadores Registados</span>
            <span class="text-2xl font-black text-theme block mt-1">{{ $totalUsers }}</span>
        </div>
        <div class="glass-card p-4 rounded-xl text-center">
            <span class="text-xs text-theme-secondary uppercase font-bold tracking-wider block">Confirmações Hoje</span>
            <span class="text-2xl font-black text-emerald-400 block mt-1">{{ $reportsToday }}</span>
        </div>
        <div class="glass-card p-4 rounded-xl text-center">
            <span class="text-xs text-theme-secondary uppercase font-bold tracking-wider block">Previsões Recalculadas</span>
            <span class="text-2xl font-black text-blue-400 block mt-1">{{ $totalPredictions }}</span>
        </div>
        <div class="glass-card p-4 rounded-xl text-center">
            <span class="text-xs text-theme-secondary uppercase font-bold tracking-wider block">Avisos Ativos</span>
            <span class="text-2xl font-black text-rose-400 block mt-1">{{ $activeAlertsCount }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Left: User Management Panel (Span 7) -->
        <div class="lg:col-span-7 space-y-6">
            <div class="glass-card p-6 rounded-3xl space-y-4">
                <div class="flex justify-between items-center gap-4 flex-wrap">
                    <h3 class="text-lg font-bold text-theme">Gestão de Utilizadores</h3>
                    <label for="search-user" class="sr-only">Pesquisar utilizadores</label>
                    <input 
                        id="search-user"
                        type="text" 
                        wire:model.live.debounce.300ms="searchUser" 
                        placeholder="Pesquisar por username, nome ou email..." 
                        class="glass-input px-3.5 py-1.5 rounded-xl text-sm w-full sm:w-64"
                    />
                </div>

                @if(session()->has('user_action'))
                    <div class="p-2.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs rounded-xl">
                        {{ session('user_action') }}
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm text-theme" aria-label="Gestão de utilizadores">
                        <caption class="sr-only">Lista de utilizadores registados</caption>
                        <thead>
                            <tr class="border-b border-theme-subtle text-theme-secondary font-bold uppercase">
                                <th scope="col" class="py-2.5">Nome / Username</th>
                                <th scope="col" class="py-2.5">Email</th>
                                <th scope="col" class="py-2.5 text-center">Pontos</th>
                                <th scope="col" class="py-2.5 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-theme-subtle">
                            @foreach($usersList as $usr)
                                <tr class="hover:bg-theme-card/50">
                                    <td class="py-3 font-semibold text-theme">
                                        {{ $usr->name }}
                                        <span class="block text-sm text-theme-muted">@ {{ $usr->username }}</span>
                                    </td>
                                    <td class="py-3 text-theme-secondary">{{ $usr->email }}</td>
                                    <td class="py-3 text-center font-bold text-blue-400">{{ $usr->score }}</td>
                                    <td class="py-3 text-right space-x-1.5 whitespace-nowrap">
                                        <button wire:click="selectUser({{ $usr->id }})" class="bg-blue-600/10 hover:bg-blue-600 active:scale-95 text-blue-400 hover:text-white px-2.5 py-1.5 rounded-lg border border-blue-500/20 hover:border-blue-500 transition-all text-xs sm:text-sm">
                                            Ajustar
                                        </button>
                                        <button wire:click="toggleSuspension({{ $usr->id }})" class="px-2.5 py-1.5 rounded-lg transition-all text-xs sm:text-sm {{ $usr->is_suspended ? 'bg-emerald-600/10 hover:bg-emerald-600 text-emerald-400 hover:text-white border border-emerald-500/20 hover:border-emerald-500' : 'bg-rose-600/10 hover:bg-rose-600 text-rose-400 hover:text-white border border-rose-500/20 hover:border-rose-500' }}">
                                            {{ $usr->is_suspended ? 'Reativar' : 'Suspender' }}
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Score Override Modal / Form Block -->
            @if($selectedUser)
                <div class="glass-card p-6 rounded-3xl border border-blue-500/30 bg-blue-950/10 space-y-4">
                    <div class="flex justify-between items-center">
                        <h3 class="text-sm font-bold text-blue-300 uppercase tracking-wider">Ajuste de Pontos para: @ {{ $selectedUser->username }}</h3>
                        <button wire:click="$set('selectedUser', null)" class="text-sm text-theme-secondary hover:text-theme">Cancelar</button>
                    </div>

                    @if(session()->has('adjust_success'))
                        <div class="p-2.5 bg-emerald-500/20 border border-emerald-500/30 text-emerald-200 text-xs rounded-xl">
                            {{ session('adjust_success') }}
                        </div>
                    @endif

                    <form wire:submit.prevent="adjustScore" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                        <div class="space-y-1">
                                    <label for="adjust-points" class="text-xs text-theme-secondary font-bold block uppercase tracking-wider">Nova Pontuação</label>
                            <input id="adjust-points" type="number" wire:model="adjustmentPoints" class="w-full glass-input px-3 py-2 rounded-xl text-sm" />
                            @error('adjustmentPoints') <span class="text-[10px] text-rose-400 block" role="alert">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1">
                                    <label for="adjust-justification" class="text-xs text-theme-secondary font-bold block uppercase tracking-wider">Justificação</label>
                            <input id="adjust-justification" type="text" wire:model="justification" placeholder="Ex: Correção de falsa penalização..." class="w-full glass-input px-3 py-2 rounded-xl text-sm" />
                            @error('justification') <span class="text-[10px] text-rose-400 block" role="alert">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 rounded-xl text-xs transition-colors">
                            Guardar Ajuste
                        </button>
                    </form>
                </div>
            @endif

            <!-- Adjustments audit history log -->
            <div class="glass-card p-6 rounded-3xl space-y-4">
                <h3 class="text-lg font-bold text-theme">Histórico de Ajustes Administrativos</h3>
                <div class="space-y-2">
                    @forelse($adjustmentsList as $adj)
                        <div class="bg-theme-card border border-theme-subtle p-3 rounded-2xl flex flex-col sm:flex-row justify-between sm:items-center text-sm gap-2">
                            <div>
                                <span class="font-bold text-theme block">Ajuste de {{ $adj->difference > 0 ? '+' : '' }}{{ $adj->difference }} pontos</span>
                                <span class="text-xs text-theme-secondary block">Utilizador: @ {{ $adj->target->username }} &bull; Justificação: "{{ $adj->justification }}"</span>
                            </div>
                            <div class="text-[10px] text-theme-muted sm:text-right">
                                <span>Por: @ {{ $adj->admin->username }}</span>
                                <span class="block">{{ $adj->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-theme-muted text-center py-4">Sem registos de ajustes na auditoria de backoffice.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right: Beach Profile & Data Sync (Span 5) -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Beach Profile Manager Card -->
            <div class="glass-card p-6 rounded-3xl space-y-4">
                <h3 class="text-sm font-bold text-theme uppercase tracking-wider">Perfis de Previsão por Praia</h3>
                
                @if(session()->has('beach_profile_success'))
                    <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs rounded-xl font-semibold">
                        ✔️ {{ session('beach_profile_success') }}
                    </div>
                @endif

                <div class="space-y-3">
                    <div class="space-y-1">
                        <label class="text-xs text-theme-secondary font-bold block uppercase tracking-wider">Selecionar Praia para Configurar</label>
                        <select wire:change="selectBeachForEdit($event.target.value)" class="w-full glass-input px-3.5 py-2.5 rounded-xl">
                            <option value="">-- Selecione uma Praia --</option>
                            @foreach($beachesList as $bc)
                                <option value="{{ $bc->id }}" {{ $selectedBeachId == $bc->id ? 'selected' : '' }}>{{ $bc->name }} ({{ $bc->type }})</option>
                            @endforeach
                        </select>
                    </div>

                    @if($selectedBeachId)
                        <form wire:submit.prevent="saveBeachProfile" class="space-y-3 pt-3 border-t border-theme-subtle">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-1">
                                    <label class="text-xs text-theme-secondary font-bold block uppercase tracking-wider">Tipo de Praia</label>
                                    <select wire:model="beachType" class="w-full glass-input px-3.5 py-2.5 rounded-xl">
                                        <option value="oceanic">Oceânica (Exposta)</option>
                                        <option value="estuarine">Estuarina (Rio/Foz)</option>
                                        <option value="fluvial">Fluvial (Rio/Interior)</option>
                                        <option value="lagoon">Lagunar (Lagoa)</option>
                                    </select>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs text-theme-secondary font-bold block uppercase tracking-wider">Fator de Exposição</label>
                                    <input type="number" step="0.05" min="0.0" max="5.0" wire:model="exposureFactor" class="w-full glass-input px-3.5 py-2.5 rounded-xl text-sm" />
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-1">
                                    <label class="text-xs text-theme-secondary font-bold block uppercase tracking-wider">Fator de Abrigo</label>
                                    <input type="number" step="0.05" min="0.1" max="10.0" wire:model="shelterFactor" class="w-full glass-input px-3.5 py-2.5 rounded-xl text-sm" />
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs text-theme-secondary font-bold block uppercase tracking-wider">Peso Ondulação</label>
                                    <input type="number" step="0.1" min="0.0" max="2.0" wire:model="waveHeightWeight" class="w-full glass-input px-3.5 py-2.5 rounded-xl text-sm" />
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-1">
                                    <label class="text-xs text-theme-secondary font-bold block uppercase tracking-wider">Peso Vento</label>
                                    <input type="number" step="0.1" min="0.0" max="2.0" wire:model="windWeight" class="w-full glass-input px-3.5 py-2.5 rounded-xl text-sm" />
                                </div>
                            </div>

                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 active:scale-[0.98] text-white font-bold py-2.5 rounded-xl text-sm transition-all touch-target">
                                Salvar Perfil de Previsão
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Operations details logs status -->
            <div class="glass-card p-6 rounded-3xl space-y-4">
                <h3 class="text-sm font-bold text-theme uppercase tracking-wider">Estado Operacional de APIs Externas</h3>
                
                @if(session()->has('sync_success'))
                    <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs rounded-xl font-semibold">
                        ✔️ {{ session('sync_success') }}
                    </div>
                @endif
                @if(session()->has('sync_error'))
                    <div class="p-3 bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs rounded-xl font-semibold">
                        ❌ {{ session('sync_error') }}
                    </div>
                @endif

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between items-center p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300">
                        <span>🌤️ IPMA Meteo & Swell Forecast API</span>
                        <span class="font-extrabold text-sm uppercase">Operacional</span>
                    </div>

                    <div class="flex justify-between items-center p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300">
                        <span>💧 InfoÁgua Balnear Quality API</span>
                        <span class="font-extrabold text-sm uppercase">Operacional</span>
                    </div>

                    <div class="flex justify-between items-center p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300">
                        <span>🏨 TripAdvisor Location Partner API</span>
                        <span class="font-extrabold text-sm uppercase">Operacional (Cache)</span>
                    </div>

                    <div class="flex justify-between items-center p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300">
                        <span>🍴 TheFork Booking API Wrapper</span>
                        <span class="font-extrabold text-sm uppercase">Operacional (Cache)</span>
                    </div>
                </div>

                <div class="pt-4 border-t border-theme-subtle space-y-2">
                    <span class="text-xs text-theme-secondary font-bold block uppercase tracking-wider">Ações de Sincronização Manual</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <button wire:click="syncIpmaData" wire:loading.attr="disabled" class="bg-blue-600 hover:bg-blue-500 active:scale-[0.98] text-white font-bold py-3 sm:py-2 rounded-xl text-sm transition-all flex items-center justify-center gap-1.5 disabled:opacity-50 touch-target">
                            <span wire:loading wire:target="syncIpmaData" class="animate-spin inline-block h-3 w-3 border-2 border-white border-t-transparent rounded-full"></span>
                            <span>🔄 Sincronizar IPMA</span>
                        </button>
                        <button wire:click="syncWaterQualityData" wire:loading.attr="disabled" class="bg-blue-600 hover:bg-blue-500 active:scale-[0.98] text-white font-bold py-3 sm:py-2 rounded-xl text-sm transition-all flex items-center justify-center gap-1.5 disabled:opacity-50 touch-target">
                            <span wire:loading wire:target="syncWaterQualityData" class="animate-spin inline-block h-3 w-3 border-2 border-white border-t-transparent rounded-full"></span>
                            <span>🔄 Sincronizar InfoÁgua</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
