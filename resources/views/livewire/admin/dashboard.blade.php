<div class="space-y-6" x-data="{ activeTab: 'visao-geral' }">
    @section('title', 'Administração - CheckPraia')

    {{-- Header --}}
    <div class="glass-card p-5 sm:p-6 rounded-2xl border border-teal-500/20 bg-teal-950/10">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-teal-300 tracking-tight flex items-center gap-2">
                    <span>⚙️</span> Painel de Administração
                </h1>
                <p class="text-sm text-theme-secondary mt-0.5">Gerir utilizadores, praias, definições e operações do sistema CheckPraia.</p>
            </div>
            <span class="text-xs font-mono bg-theme-card border border-teal-500/20 px-3 py-1.5 rounded-lg text-teal-400 shrink-0 self-start">Acesso Administrativo</span>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session()->has('sync_success'))
        <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs rounded-xl font-semibold">✔️ {{ session('sync_success') }}</div>
    @endif
    @if(session()->has('sync_error'))
        <div class="p-3 bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs rounded-xl font-semibold">❌ {{ session('sync_error') }}</div>
    @endif
    @if(session()->has('user_action'))
        <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs rounded-xl font-semibold">ℹ️ {{ session('user_action') }}</div>
    @endif
    @if(session()->has('beach_action'))
        <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs rounded-xl font-semibold">🏖️ {{ session('beach_action') }}</div>
    @endif
    @if(session()->has('settings_success'))
        <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs rounded-xl font-semibold">⚙️ {{ session('settings_success') }}</div>
    @endif
    @if(session()->has('cache_success'))
        <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs rounded-xl font-semibold">🧹 {{ session('cache_success') }}</div>
    @endif
    @if(session()->has('adjust_success'))
        <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs rounded-xl font-semibold">⭐ {{ session('adjust_success') }}</div>
    @endif

    {{-- Tab Navigation --}}
    <div class="flex flex-wrap gap-1.5 border-b border-theme-subtle pb-3 overflow-x-auto">
        <button @click="activeTab = 'visao-geral'" :class="activeTab === 'visao-geral' ? 'bg-blue-600/20 border-blue-500/40 text-blue-300' : 'bg-theme-card border-theme-subtle text-theme-secondary hover:text-theme hover:border-theme-medium'" class="shrink-0 px-4 py-2 rounded-xl text-sm font-bold border transition-all flex items-center gap-1.5 touch-target">
            <span>📊</span> Visão Geral
        </button>
        <button @click="activeTab = 'utilizadores'" :class="activeTab === 'utilizadores' ? 'bg-blue-600/20 border-blue-500/40 text-blue-300' : 'bg-theme-card border-theme-subtle text-theme-secondary hover:text-theme hover:border-theme-medium'" class="shrink-0 px-4 py-2 rounded-xl text-sm font-bold border transition-all flex items-center gap-1.5 touch-target">
            <span>👥</span> Utilizadores
        </button>
        <button @click="activeTab = 'praias'" :class="activeTab === 'praias' ? 'bg-blue-600/20 border-blue-500/40 text-blue-300' : 'bg-theme-card border-theme-subtle text-theme-secondary hover:text-theme hover:border-theme-medium'" class="shrink-0 px-4 py-2 rounded-xl text-sm font-bold border transition-all flex items-center gap-1.5 touch-target">
            <span>🏖️</span> Praias
        </button>
        <button @click="activeTab = 'sincronizacao'" :class="activeTab === 'sincronizacao' ? 'bg-blue-600/20 border-blue-500/40 text-blue-300' : 'bg-theme-card border-theme-subtle text-theme-secondary hover:text-theme hover:border-theme-medium'" class="shrink-0 px-4 py-2 rounded-xl text-sm font-bold border transition-all flex items-center gap-1.5 touch-target">
            <span>🔄</span> Sincronização
        </button>
        <button @click="activeTab = 'definicoes'" :class="activeTab === 'definicoes' ? 'bg-blue-600/20 border-blue-500/40 text-blue-300' : 'bg-theme-card border-theme-subtle text-theme-secondary hover:text-theme hover:border-theme-medium'" class="shrink-0 px-4 py-2 rounded-xl text-sm font-bold border transition-all flex items-center gap-1.5 touch-target">
            <span>⚙️</span> Definições
        </button>
        <button @click="activeTab = 'sistema'" :class="activeTab === 'sistema' ? 'bg-blue-600/20 border-blue-500/40 text-blue-300' : 'bg-theme-card border-theme-subtle text-theme-secondary hover:text-theme hover:border-theme-medium'" class="shrink-0 px-4 py-2 rounded-xl text-sm font-bold border transition-all flex items-center gap-1.5 touch-target">
            <span>🖥️</span> Sistema
        </button>
    </div>

    {{-- ════════════════════════════════════════ TAB 1: VISÃO GERAL ════════════════════════════════════════ --}}
    <div x-show="activeTab === 'visao-geral'" x-cloak class="space-y-6">

        {{-- Main metrics --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="glass-card p-4 rounded-xl text-center">
                <span class="text-xs text-theme-secondary uppercase font-bold tracking-wider block">Utilizadores</span>
                <span class="text-2xl font-black text-theme block mt-1">{{ $totalUsers }}</span>
                <span class="text-sm text-theme-muted block mt-0.5">{{ $adminUsers }} admin · {{ $suspendedUsers }} suspensos</span>
            </div>
            <div class="glass-card p-4 rounded-xl text-center">
                <span class="text-xs text-theme-secondary uppercase font-bold tracking-wider block">Confirmações Hoje</span>
                <span class="text-2xl font-black text-emerald-400 block mt-1">{{ $reportsToday }}</span>
            </div>
            <div class="glass-card p-4 rounded-xl text-center">
                <span class="text-xs text-theme-secondary uppercase font-bold tracking-wider block">Previsões (24h)</span>
                <span class="text-2xl font-black text-blue-400 block mt-1">{{ $totalPredictions }}</span>
            </div>
            <div class="glass-card p-4 rounded-xl text-center">
                <span class="text-xs text-theme-secondary uppercase font-bold tracking-wider block">Avisos Ativos</span>
                <span class="text-2xl font-black text-rose-400 block mt-1">{{ $activeAlertsCount }}</span>
            </div>
        </div>

        {{-- Secondary metrics --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="glass-card p-4 rounded-xl text-center">
                <span class="text-xs text-theme-secondary uppercase font-bold tracking-wider block">Total Praias</span>
                <span class="text-2xl font-black text-theme block mt-1">{{ $totalBeaches }}</span>
                <span class="text-sm text-theme-muted block mt-0.5">{{ $activeBeaches }} ativas</span>
            </div>
            <div class="glass-card p-4 rounded-xl text-center">
                <span class="text-xs text-theme-secondary uppercase font-bold tracking-wider block">Praias com Estado</span>
                <span class="text-2xl font-black text-teal-400 block mt-1">{{ $beachesWithStatus }}</span>
                <span class="text-sm text-theme-muted block mt-0.5">de {{ $totalBeaches }} totais</span>
            </div>
            <div class="glass-card p-4 rounded-xl text-center">
                <span class="text-xs text-theme-secondary uppercase font-bold tracking-wider block">Fila (Queue)</span>
                @if($systemInfo['queue_size'] >= 0)
                    <span class="text-2xl font-black text-amber-400 block mt-1">{{ $systemInfo['queue_size'] }}</span>
                    <span class="text-sm text-theme-muted block mt-0.5">tarefas pendentes</span>
                @else
                    <span class="text-sm font-black text-amber-400 block mt-1">N/A</span>
                @endif
            </div>
            <div class="glass-card p-4 rounded-xl text-center">
                <span class="text-xs text-theme-secondary uppercase font-bold tracking-wider block">Tarefas Falhadas</span>
                @if($systemInfo['failed_jobs'] >= 0)
                    <span class="text-2xl font-black text-rose-400 block mt-1">{{ $systemInfo['failed_jobs'] }}</span>
                @else
                    <span class="text-sm font-black text-rose-400 block mt-1">N/A</span>
                @endif
            </div>
        </div>

        {{-- Flag distribution --}}
        <div class="glass-card p-5 rounded-2xl space-y-3">
            <h2 class="text-sm font-bold text-theme uppercase tracking-wider flex items-center gap-1.5">
                <span>🏁</span> Distribuição de Bandeiras
            </h2>
            <p class="text-xs text-theme-muted">Estado atual das bandeiras nas praias com informação disponível.</p>
            <div class="flex flex-wrap gap-2">
                @php
                    $flagLabels = ['green' => ['Verde', 'bg-emerald-500', 'text-emerald-400'], 'yellow' => ['Amarela', 'bg-amber-500', 'text-amber-400'], 'red' => ['Vermelha', 'bg-rose-500', 'text-rose-400'], 'blue_or_neutral' => ['Fora de Época', 'bg-blue-500', 'text-blue-400'], 'gray' => ['Sem Info', 'bg-slate-500', 'text-slate-400']];
                @endphp
                @foreach($flagLabels as $key => [$label, $bgClass, $textClass])
                    <div class="flex items-center gap-2 bg-theme-card border border-theme-subtle px-3 py-2 rounded-xl">
                        <span class="w-3 h-3 rounded-full {{ $bgClass }}"></span>
                        <span class="text-sm font-semibold text-theme">{{ $label }}</span>
                        <span class="text-sm font-bold {{ $textClass }}">{{ $flagDistribution[$key] ?? 0 }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Últimos ajustes --}}
        <div class="glass-card p-5 rounded-2xl space-y-3">
            <h2 class="text-sm font-bold text-theme uppercase tracking-wider flex items-center gap-1.5">
                <span>📋</span> Últimos Ajustes Administrativos
            </h2>
            <div class="space-y-2">
                @forelse($adjustmentsList as $adj)
                    <div class="bg-theme-card border border-theme-subtle p-3 rounded-xl flex flex-col sm:flex-row justify-between sm:items-center text-sm gap-1.5">
                        <div>
                            <span class="font-bold text-theme">{{ $adj->difference > 0 ? '+' : '' }}{{ $adj->difference }} pts</span>
                            <span class="text-theme-secondary"> para </span>
                            <span class="font-semibold text-blue-400">{{ '@' . $adj->target->username }}</span>
                            <span class="text-theme-muted text-xs block sm:inline sm:ml-2">"{{ $adj->justification }}"</span>
                        </div>
                        <div class="text-xs text-theme-muted flex items-center gap-2 shrink-0">
                            <span>por {{ $adj->admin->name }}</span>
                            <span>·</span>
                            <span>{{ $adj->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-theme-muted text-center py-4">Sem ajustes registados.</p>
                @endforelse
            </div>
        </div>

        {{-- Atalhos rápidos --}}
        <div class="glass-card p-5 rounded-2xl space-y-3">
            <h2 class="text-sm font-bold text-theme uppercase tracking-wider flex items-center gap-1.5">
                <span>⚡</span> Atalhos Rápidos
            </h2>
            <p class="text-xs text-theme-muted">Ações frequentes num clique.</p>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                <button wire:click="syncIpmaDataSync" wire:loading.attr="disabled" class="bg-amber-600 hover:bg-amber-500 active:scale-95 text-white font-bold py-3 rounded-xl text-xs transition-all disabled:opacity-50 touch-target flex items-center justify-center gap-1.5">
                    <span wire:loading wire:target="syncIpmaDataSync" class="animate-spin h-3 w-3 border-2 border-white border-t-transparent rounded-full"></span>
                    <span>⚡ Sincronizar IPMA</span>
                </button>
                <button wire:click="syncWaterQualityDataSync" wire:loading.attr="disabled" class="bg-amber-600 hover:bg-amber-500 active:scale-95 text-white font-bold py-3 rounded-xl text-xs transition-all disabled:opacity-50 touch-target flex items-center justify-center gap-1.5">
                    <span wire:loading wire:target="syncWaterQualityDataSync" class="animate-spin h-3 w-3 border-2 border-white border-t-transparent rounded-full"></span>
                    <span>⚡ Sincronizar InfoÁgua</span>
                </button>
                <button wire:click="processQueue" wire:loading.attr="disabled" class="bg-indigo-600 hover:bg-indigo-500 active:scale-95 text-white font-bold py-3 rounded-xl text-xs transition-all disabled:opacity-50 touch-target flex items-center justify-center gap-1.5">
                    <span wire:loading wire:target="processQueue" class="animate-spin h-3 w-3 border-2 border-white border-t-transparent rounded-full"></span>
                    <span>⚙️ Processar Fila</span>
                </button>
                <button wire:click="clearCache" wire:loading.attr="disabled" class="bg-slate-600 hover:bg-slate-500 active:scale-95 text-white font-bold py-3 rounded-xl text-xs transition-all disabled:opacity-50 touch-target flex items-center justify-center gap-1.5">
                    <span wire:loading wire:target="clearCache" class="animate-spin h-3 w-3 border-2 border-white border-t-transparent rounded-full"></span>
                    <span>🧹 Limpar Cache</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════ TAB 2: UTILIZADORES ════════════════════════════════════════ --}}
    <div x-show="activeTab === 'utilizadores'" x-cloak class="space-y-6">
        <div class="glass-card p-5 rounded-2xl space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-theme flex items-center gap-1.5">
                        <span>👥</span> Gestão de Utilizadores
                    </h2>
                    <p class="text-xs text-theme-muted">Pesquisar, ajustar pontuações, suspender/reativar e gerir privilégios de administrador.</p>
                </div>
                <div class="flex gap-2">
                    <input type="text" wire:model.live.debounce.300ms="searchUser" placeholder="Nome, username ou email..." class="bg-theme-input border border-theme-subtle px-3 py-2 rounded-xl text-sm text-theme placeholder:text-theme-muted focus:outline-none focus:border-blue-500/50 w-full sm:w-64" />
                    @if($searchUser)
                        <button wire:click="resetUserSearch" class="text-sm text-theme-muted hover:text-theme px-2">✕</button>
                    @endif
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-theme-subtle">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-theme-subtle bg-theme-card/50">
                            <th class="py-2.5 px-3 text-xs font-bold text-theme-secondary uppercase tracking-wider">Utilizador</th>
                            <th class="py-2.5 px-3 text-xs font-bold text-theme-secondary uppercase tracking-wider hidden sm:table-cell">Email</th>
                            <th class="py-2.5 px-3 text-xs font-bold text-theme-secondary uppercase tracking-wider text-center">Pontos</th>
                            <th class="py-2.5 px-3 text-xs font-bold text-theme-secondary uppercase tracking-wider text-center hidden sm:table-cell">Estado</th>
                            <th class="py-2.5 px-3 text-xs font-bold text-theme-secondary uppercase tracking-wider text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-theme-subtle">
                        @forelse($usersList as $usr)
                            <tr class="hover:bg-theme-card/30 transition-colors">
                                <td class="py-3 px-3">
                                    <div class="font-semibold text-theme flex items-center gap-1.5">
                                        {{ $usr->name }}
                                        @if($usr->is_admin)
                                            <span class="text-sm bg-teal-600/20 text-teal-400 px-1.5 py-0.5 rounded font-bold leading-none">Admin</span>
                                        @endif
                                    </div>
                                    <span class="text-xs text-theme-muted">@ {{ $usr->username }}</span>
                                </td>
                                <td class="py-3 px-3 text-theme-secondary text-xs hidden sm:table-cell">{{ $usr->email }}</td>
                                <td class="py-3 px-3 text-center font-bold">
                                    <span class="{{ $usr->is_suspended ? 'text-theme-muted' : 'text-blue-400' }}">{{ $usr->score }}</span>
                                </td>
                                <td class="py-3 px-3 text-center hidden sm:table-cell">
                                    @if($usr->is_suspended)
                                        <span class="text-sm bg-rose-600/20 text-rose-400 px-2 py-0.5 rounded font-bold">Suspenso</span>
                                    @else
                                        <span class="text-sm bg-emerald-600/20 text-emerald-400 px-2 py-0.5 rounded font-bold">Ativo</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <div class="flex items-center justify-end gap-1 flex-wrap">
                                        <button wire:click="selectUser({{ $usr->id }})" class="bg-blue-600/10 hover:bg-blue-600 active:scale-95 text-blue-400 hover:text-white px-2.5 py-1.5 rounded-lg border border-blue-500/20 hover:border-blue-500 transition-all text-xs font-semibold">⭐ Ajustar</button>
                                        <button wire:click="toggleSuspension({{ $usr->id }})" class="px-2.5 py-1.5 rounded-lg transition-all text-xs font-semibold {{ $usr->is_suspended ? 'bg-emerald-600/10 hover:bg-emerald-600 text-emerald-400 hover:text-white border border-emerald-500/20' : 'bg-rose-600/10 hover:bg-rose-600 text-rose-400 hover:text-white border border-rose-500/20' }}">
                                            {{ $usr->is_suspended ? 'Reativar' : 'Suspender' }}
                                        </button>
                                        @if($usr->is_admin)
                                            <button wire:click="removeAdmin({{ $usr->id }})" wire:confirm="Remover privilégios de admin de {{ $usr->name }}?" class="bg-amber-600/10 hover:bg-amber-600 active:scale-95 text-amber-400 hover:text-white px-2.5 py-1.5 rounded-lg border border-amber-500/20 text-xs font-semibold">👑 Remover Admin</button>
                                        @else
                                            <button wire:click="makeAdmin({{ $usr->id }})" wire:confirm="Promover {{ $usr->name }} a administrador?" class="bg-purple-600/10 hover:bg-purple-600 active:scale-95 text-purple-400 hover:text-white px-2.5 py-1.5 rounded-lg border border-purple-500/20 text-xs font-semibold">👑 Admin</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-sm text-theme-muted">Nenhum utilizador encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($usersList->hasPages())
                <div class="pt-2">{{ $usersList->links() }}</div>
            @endif

            {{-- Audit History --}}
            <div class="pt-4 border-t border-theme-subtle space-y-2">
                <h3 class="text-xs font-bold text-theme-secondary uppercase tracking-wider flex items-center gap-1.5">
                    <span>📋</span> Histórico de Ajustes de Pontuação
                </h3>
                <div class="space-y-1.5 max-h-48 overflow-y-auto">
                    @forelse($adjustmentsList as $adj)
                        <div class="text-xs text-theme-secondary flex items-center gap-2 py-1.5 px-2 rounded-lg bg-theme-card/30">
                            <span class="font-bold {{ $adj->difference > 0 ? 'text-emerald-400' : 'text-rose-400' }}">{{ $adj->difference > 0 ? '+' : '' }}{{ $adj->difference }}</span>
                            <span>→</span>
                            <span class="font-semibold text-theme">{{ $adj->target->name }}</span>
                            <span class="text-theme-muted">· "{{ $adj->justification }}"</span>
                            <span class="text-theme-muted ml-auto shrink-0">{{ $adj->created_at->format('d/m/Y') }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-theme-muted text-center py-3">Nenhum ajuste registado.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Score adjustment form --}}
        @if($selectedUser)
            <div class="glass-card p-5 rounded-2xl border border-blue-500/30 bg-blue-950/10 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-blue-300 flex items-center gap-1.5">
                        <span>⭐</span> Ajustar Pontuação: <span class="text-white">{{ $selectedUser->name }}</span>
                        <span class="text-theme-muted font-normal">(@{{ $selectedUser->username }})</span>
                    </h3>
                    <span class="text-xs text-theme-muted">Atual: <span class="font-bold text-blue-400">{{ $selectedUser->score }}</span> pts</span>
                </div>
                <p class="text-xs text-theme-muted">Define a nova pontuação e explica o motivo. O histórico fica registado na auditoria.</p>

                <form wire:submit.prevent="adjustScore" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                    <div class="space-y-1">
                        <label for="adjust-points" class="text-xs text-theme-secondary font-bold block uppercase tracking-wider">Nova Pontuação</label>
                        <input id="adjust-points" type="number" wire:model="adjustmentPoints" class="w-full bg-theme-input border border-theme-subtle px-3 py-2 rounded-xl text-sm text-theme focus:outline-none focus:border-blue-500/50" />
                        @error('adjustmentPoints') <span class="text-xs text-rose-400 block mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-1">
                        <label for="adjust-justification" class="text-xs text-theme-secondary font-bold block uppercase tracking-wider">Justificação</label>
                        <input id="adjust-justification" type="text" wire:model="justification" placeholder="Ex: Correção de penalização indevida" class="w-full bg-theme-input border border-theme-subtle px-3 py-2 rounded-xl text-sm text-theme focus:outline-none focus:border-blue-500/50" />
                        @error('justification') <span class="text-xs text-rose-400 block mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-500 active:scale-95 text-white font-bold py-2.5 rounded-xl text-sm transition-colors">Guardar Ajuste</button>
                        <button type="button" wire:click="$set('selectedUser', null)" class="px-3 py-2.5 rounded-xl text-sm text-theme-secondary hover:text-theme bg-theme-card border border-theme-subtle">Cancelar</button>
                    </div>
                </form>
            </div>
        @endif
    </div>

    {{-- ════════════════════════════════════════ TAB 3: PRAIAS ════════════════════════════════════════ --}}
    <div x-show="activeTab === 'praias'" x-cloak class="space-y-6">
        <div class="glass-card p-5 rounded-2xl space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-theme flex items-center gap-1.5">
                        <span>🏖️</span> Gestão de Praias
                    </h2>
                    <p class="text-xs text-theme-muted">Ativar ou desativar praias. Uma praia desativada não aparece no mapa nem na lista pública.</p>
                </div>
                <div class="flex items-center gap-2">
                    <label class="flex items-center gap-2 text-xs text-theme-secondary cursor-pointer">
                        <input type="checkbox" wire:model.live="showInactiveOnly" class="rounded border-theme-subtle bg-theme-input">
                        Só inativas
                    </label>
                    <input type="text" wire:model.live.debounce.300ms="searchBeach" placeholder="Nome, município ou região..." class="bg-theme-input border border-theme-subtle px-3 py-2 rounded-xl text-sm text-theme placeholder:text-theme-muted focus:outline-none focus:border-blue-500/50 w-full sm:w-64" />
                    @if($searchBeach || $showInactiveOnly)
                        <button wire:click="resetBeachSearch" class="text-sm text-theme-muted hover:text-theme px-2">✕</button>
                    @endif
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-theme-subtle">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-theme-subtle bg-theme-card/50">
                            <th class="py-2.5 px-3 text-xs font-bold text-theme-secondary uppercase tracking-wider">Nome</th>
                            <th class="py-2.5 px-3 text-xs font-bold text-theme-secondary uppercase tracking-wider hidden sm:table-cell">Município</th>
                            <th class="py-2.5 px-3 text-xs font-bold text-theme-secondary uppercase tracking-wider hidden md:table-cell">Região</th>
                            <th class="py-2.5 px-3 text-xs font-bold text-theme-secondary uppercase tracking-wider text-center">Estado</th>
                            <th class="py-2.5 px-3 text-xs font-bold text-theme-secondary uppercase tracking-wider text-center hidden sm:table-cell">Bandeira</th>
                            <th class="py-2.5 px-3 text-xs font-bold text-theme-secondary uppercase tracking-wider text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-theme-subtle">
                        @forelse($beachesList as $beach)
                            <tr class="hover:bg-theme-card/30 transition-colors {{ !$beach->is_active ? 'opacity-60' : '' }}">
                                <td class="py-3 px-3 font-semibold text-theme">{{ $beach->name }}</td>
                                <td class="py-3 px-3 text-theme-secondary text-xs hidden sm:table-cell">{{ $beach->municipality }}</td>
                                <td class="py-3 px-3 text-theme-secondary text-xs hidden md:table-cell">{{ $beach->region }}</td>
                                <td class="py-3 px-3 text-center">
                                    @if($beach->is_active)
                                        <span class="text-sm bg-emerald-600/20 text-emerald-400 px-2 py-0.5 rounded font-bold">Ativa</span>
                                    @else
                                        <span class="text-sm bg-rose-600/20 text-rose-400 px-2 py-0.5 rounded font-bold">Inativa</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-center hidden sm:table-cell">
                                    @if($beach->currentStatus)
                                        @php
                                            $flagStyles = ['green' => 'bg-emerald-500', 'yellow' => 'bg-amber-500', 'red' => 'bg-rose-500', 'blue_or_neutral' => 'bg-blue-500', 'gray' => 'bg-slate-500'];
                                            $style = $flagStyles[$beach->currentStatus->flag] ?? 'bg-slate-500';
                                        @endphp
                                        <span class="w-2.5 h-2.5 rounded-full inline-block {{ $style }}"></span>
                                    @else
                                        <span class="text-xs text-theme-muted">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button wire:click="confirmResetVotes({{ $beach->id }})"
                                                class="px-2 py-1.5 rounded-lg transition-all text-xs font-semibold bg-amber-600/10 hover:bg-amber-600 text-amber-400 hover:text-white border border-amber-500/20"
                                                title="Cancelar todos os votos de hoje">🗑️</button>
                                        <button wire:click="showOverride({{ $beach->id }})"
                                                class="px-2 py-1.5 rounded-lg transition-all text-xs font-semibold bg-violet-600/10 hover:bg-violet-600 text-violet-400 hover:text-white border border-violet-500/20"
                                                title="Sobrepor bandeira">✏️</button>
                                        <button wire:click="toggleBeachActive({{ $beach->id }})" wire:confirm="{{ $beach->is_active ? 'Desativar' : 'Ativar' }} {{ $beach->name }}?" class="px-2.5 py-1.5 rounded-lg transition-all text-xs font-semibold {{ $beach->is_active ? 'bg-rose-600/10 hover:bg-rose-600 text-rose-400 hover:text-white border border-rose-500/20' : 'bg-emerald-600/10 hover:bg-emerald-600 text-emerald-400 hover:text-white border border-emerald-500/20' }}">
                                            {{ $beach->is_active ? 'Desativar' : 'Ativar' }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-sm text-theme-muted">Nenhuma praia encontrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($beachesList->hasPages())
                <div class="pt-2">{{ $beachesList->links() }}</div>
            @endif
        </div>

        {{-- Modal: Confirmar reset de votos --}}
        @if($confirmResetBeachId)
            @php $beach = \App\Models\Beach::find($confirmResetBeachId); @endphp
            @if($beach)
                <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" wire:click.self="cancelResetVotes">
                    <div class="glass-card p-6 rounded-2xl max-w-sm w-full space-y-4">
                        <div class="text-center space-y-2">
                            <span class="text-4xl block">🗑️</span>
                            <h3 class="text-lg font-bold text-theme">Cancelar votos de hoje</h3>
                            <p class="text-sm text-theme-secondary">Tens a certeza? Todos os votos de hoje em <strong>{{ $beach->name }}</strong> serão cancelados.</p>
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="resetTodayVotes({{ $beach->id }})" class="flex-1 bg-rose-600 hover:bg-rose-500 text-white font-bold py-2.5 rounded-xl text-sm transition-all active:scale-95">Sim, cancelar</button>
                            <button wire:click="cancelResetVotes" class="flex-1 bg-theme-card border border-theme-medium hover:bg-theme-elevated text-theme font-bold py-2.5 rounded-xl text-sm transition-all">Voltar</button>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        {{-- Modal: Sobrepor bandeira --}}
        @if($overrideBeachId)
            @php $beach = \App\Models\Beach::find($overrideBeachId); @endphp
            @if($beach)
                <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" wire:click.self="cancelOverride">
                    <div class="glass-card p-6 rounded-2xl max-w-sm w-full space-y-4">
                        <div class="text-center space-y-2">
                            <span class="text-4xl block">✏️</span>
                            <h3 class="text-lg font-bold text-theme">Sobrepor bandeira</h3>
                            <p class="text-sm text-theme-secondary">Escolhe a bandeira para <strong>{{ $beach->name }}</strong>.</p>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['green' => 'bg-emerald-500', 'yellow' => 'bg-amber-500', 'red' => 'bg-rose-500', 'blue_or_neutral' => 'bg-blue-500'] as $val => $color)
                                <button wire:click="$set('overrideFlag', '{{ $val }}')"
                                        class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-bold transition-all border {{ $overrideFlag === $val ? 'ring-2 ring-offset-2 ring-offset-slate-900 ring-blue-400 border-blue-400' : 'border-theme-subtle' }}"
                                        :class="{ 'ring-2 ring-offset-2 ring-offset-slate-900 ring-blue-400 border-blue-400': false }">
                                    <span class="w-3.5 h-3.5 rounded-full inline-block {{ $color }}"></span>
                                    @php
                                        $label = match($val) { 'green' => 'Verde', 'yellow' => 'Amarela', 'red' => 'Vermelha', 'blue_or_neutral' => 'Fora de Época', default => $val };
                                    @endphp
                                    <span class="text-theme">{{ $label }}</span>
                                </button>
                            @endforeach
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="applyOverride" class="flex-1 bg-violet-600 hover:bg-violet-500 text-white font-bold py-2.5 rounded-xl text-sm transition-all active:scale-95">Aplicar</button>
                            <button wire:click="cancelOverride" class="flex-1 bg-theme-card border border-theme-medium hover:bg-theme-elevated text-theme font-bold py-2.5 rounded-xl text-sm transition-all">Cancelar</button>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>

    {{-- ════════════════════════════════════════ TAB 4: SINCRONIZAÇÃO ════════════════════════════════════════ --}}
    <div x-show="activeTab === 'sincronizacao'" x-cloak class="space-y-6">
        {{-- API Status --}}
        <div class="glass-card p-5 rounded-2xl space-y-4">
            <h2 class="text-lg font-bold text-theme flex items-center gap-1.5">
                <span>🔌</span> Estado das APIs Externas
            </h2>
            <p class="text-xs text-theme-muted">Estado operacional e última sincronização de cada fonte de dados externa.</p>

            <div class="space-y-3">
                @php
                    $ipmaTimestamp = $systemInfo['last_ipma_sync'];
                    $ipmaAge = ($ipmaTimestamp !== 'Nunca') ? now()->diffInHours($ipmaTimestamp) : PHP_INT_MAX;
                    $ipmaStatus = $ipmaAge < 48 ? 'Operacional' : 'Sem dados recentes';
                    $ipmaColor = $ipmaAge < 48 ? 'text-emerald-400' : 'text-amber-400';

                    $infoaguaTimestamp = $systemInfo['last_infoagua_sync'];
                    $infoaguaAge = ($infoaguaTimestamp !== 'Nunca') ? now()->diffInHours($infoaguaTimestamp) : PHP_INT_MAX;
                    $infoaguaStatus = $infoaguaAge < 72 ? 'Operacional' : 'Sem dados recentes';
                    $infoaguaColor = $infoaguaAge < 72 ? 'text-emerald-400' : 'text-amber-400';
                @endphp

                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 rounded-xl gap-1.5" style="background: {{ $ipmaAge < 48 ? 'rgba(16,185,129,0.1)' : 'rgba(245,158,11,0.1)' }}; border-color: {{ $ipmaAge < 48 ? 'rgba(16,185,129,0.2)' : 'rgba(245,158,11,0.2)' }}; border-width: 1px; border-style: solid;">
                    <div class="flex items-center gap-2.5">
                        <span class="text-lg">🌤️</span>
                        <div>
                            <span class="font-semibold text-sm text-theme">IPMA - Meteorologia e Ondas</span>
                            <span class="text-xs text-theme-muted block">Previsões meteorológicas e de swell da IPMA/Open-Meteo</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <span class="font-extrabold text-xs uppercase {{ $ipmaColor }}">{{ $ipmaStatus }}</span>
                        <span class="text-sm text-theme-muted">Última: {{ $systemInfo['last_ipma_sync'] }}</span>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 rounded-xl gap-1.5" style="background: {{ $infoaguaAge < 72 ? 'rgba(16,185,129,0.1)' : 'rgba(245,158,11,0.1)' }}; border-color: {{ $infoaguaAge < 72 ? 'rgba(16,185,129,0.2)' : 'rgba(245,158,11,0.2)' }}; border-width: 1px; border-style: solid;">
                    <div class="flex items-center gap-2.5">
                        <span class="text-lg">💧</span>
                        <div>
                            <span class="font-semibold text-sm text-theme">InfoÁgua - Qualidade Balnear</span>
                            <span class="text-xs text-theme-muted block">Dados de qualidade da água das praias portuguesas</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <span class="font-extrabold text-xs uppercase {{ $infoaguaColor }}">{{ $infoaguaStatus }}</span>
                        <span class="text-sm text-theme-muted">Última: {{ $systemInfo['last_infoagua_sync'] }}</span>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 gap-1.5">
                    <div class="flex items-center gap-2.5">
                        <span class="text-lg">🏨</span>
                        <div>
                            <span class="font-semibold text-sm text-theme">OpenStreetMap / Overpass API</span>
                            <span class="text-xs text-theme-muted block">Restaurantes próximos (dados públicos, sem chave necessária)</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <span class="font-extrabold text-xs uppercase text-emerald-400">Ativo (Sob Demanda)</span>
                        <span class="text-sm text-theme-muted">Sob demanda</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sync Controls --}}
        <div class="glass-card p-5 rounded-2xl space-y-4">
            <h2 class="text-lg font-bold text-theme flex items-center gap-1.5">
                <span>🔄</span> Sincronização de Dados
            </h2>
            <p class="text-xs text-theme-muted">Escolhe entre execução em segundo plano (fila) ou síncrona (imediata). A opção síncrona é útil para testes ou quando a fila não está configurada.</p>

            {{-- Async / Queue --}}
            <div class="space-y-3">
                <h3 class="text-xs font-bold text-theme-secondary uppercase tracking-wider flex items-center gap-1.5">
                    <span>⏳</span> Execução em Segundo Plano (Fila/Queue)
                </h3>
                <p class="text-xs text-theme-muted">As tarefas são enviadas para a fila e processadas em segundo plano. Recomendado para execução regular.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <button wire:click="syncIpmaData" wire:loading.attr="disabled" class="bg-blue-600/20 hover:bg-blue-600/30 active:scale-95 text-blue-300 font-bold py-3 rounded-xl text-sm border border-blue-500/20 transition-all flex items-center justify-center gap-1.5 disabled:opacity-50 touch-target">
                        <span wire:loading wire:target="syncIpmaData" class="animate-spin h-3 w-3 border-2 border-blue-300 border-t-transparent rounded-full"></span>
                        <span>🌤️ Agendar IPMA (Fila)</span>
                    </button>
                    <button wire:click="syncWaterQualityData" wire:loading.attr="disabled" class="bg-blue-600/20 hover:bg-blue-600/30 active:scale-95 text-blue-300 font-bold py-3 rounded-xl text-sm border border-blue-500/20 transition-all flex items-center justify-center gap-1.5 disabled:opacity-50 touch-target">
                        <span wire:loading wire:target="syncWaterQualityData" class="animate-spin h-3 w-3 border-2 border-blue-300 border-t-transparent rounded-full"></span>
                        <span>💧 Agendar InfoÁgua (Fila)</span>
                    </button>
                </div>
                <button wire:click="processQueue" wire:loading.attr="disabled" class="w-full bg-indigo-600 hover:bg-indigo-500 active:scale-95 text-white font-bold py-2.5 rounded-xl text-sm transition-all flex items-center justify-center gap-1.5 disabled:opacity-50 touch-target">
                    <span wire:loading wire:target="processQueue" class="animate-spin h-3 w-3 border-2 border-white border-t-transparent rounded-full"></span>
                    <span>⚙️ Processar Fila Agora (Queue Worker)</span>
                </button>
            </div>

            {{-- Direct Sync --}}
            <div class="pt-4 border-t border-theme-subtle space-y-3">
                <h3 class="text-xs font-bold text-amber-400 uppercase tracking-wider flex items-center gap-1.5">
                    <span>⚡</span> Execução Síncrona (Imediata)
                </h3>
                <p class="text-xs text-theme-muted">Os dados são processados de imediato, sem fila. Pode demorar alguns segundos. Útil para testes após configuração.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <button wire:click="syncIpmaDataSync" wire:loading.attr="disabled" class="bg-amber-600 hover:bg-amber-500 active:scale-95 text-white font-bold py-3 rounded-xl text-sm transition-all flex items-center justify-center gap-1.5 disabled:opacity-50 touch-target">
                        <span wire:loading wire:target="syncIpmaDataSync" class="animate-spin h-3 w-3 border-2 border-white border-t-transparent rounded-full"></span>
                        <span>🌤️ Importar IPMA Agora</span>
                    </button>
                    <button wire:click="syncWaterQualityDataSync" wire:loading.attr="disabled" class="bg-amber-600 hover:bg-amber-500 active:scale-95 text-white font-bold py-3 rounded-xl text-sm transition-all flex items-center justify-center gap-1.5 disabled:opacity-50 touch-target">
                        <span wire:loading wire:target="syncWaterQualityDataSync" class="animate-spin h-3 w-3 border-2 border-white border-t-transparent rounded-full"></span>
                        <span>💧 Importar InfoÁgua Agora</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Database Ops --}}
        <div class="glass-card p-5 rounded-2xl space-y-4">
            <h2 class="text-lg font-bold text-theme flex items-center gap-1.5">
                <span>🗄️</span> Operações de Base de Dados
            </h2>
            <p class="text-xs text-theme-muted">Executar migrações ou seeders. ⚠️ Estas operações alteram a estrutura e os dados da base de dados.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <button wire:click="runMigrations" wire:loading.attr="disabled" wire:confirm="Tens a certeza que queres executar migrações?" class="bg-teal-600 hover:bg-teal-500 active:scale-95 text-white font-bold py-3 rounded-xl text-sm transition-all flex items-center justify-center gap-1.5 disabled:opacity-50 touch-target">
                    <span wire:loading wire:target="runMigrations" class="animate-spin h-3 w-3 border-2 border-white border-t-transparent rounded-full"></span>
                    <span>⚙️ Executar Migrações</span>
                </button>
                <button wire:click="runSeeders" wire:loading.attr="disabled" wire:confirm="Tens a certeza que queres executar seeders? Isto pode duplicar dados!" class="bg-teal-600 hover:bg-teal-500 active:scale-95 text-white font-bold py-3 rounded-xl text-sm transition-all flex items-center justify-center gap-1.5 disabled:opacity-50 touch-target">
                    <span wire:loading wire:target="runSeeders" class="animate-spin h-3 w-3 border-2 border-white border-t-transparent rounded-full"></span>
                    <span>🌱 Executar Seeders</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════ TAB 5: DEFINIÇÕES ════════════════════════════════════════ --}}
    <div x-show="activeTab === 'definicoes'" x-cloak class="space-y-6">
        <div class="glass-card p-5 rounded-2xl space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-theme flex items-center gap-1.5">
                        <span>⚙️</span> Definições do Sistema
                    </h2>
                    <p class="text-xs text-theme-muted">Pares chave-valor para configurar o comportamento do site. Cria, edita ou remove definições conforme necessário.</p>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-theme-subtle">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-theme-subtle bg-theme-card/50">
                            <th class="py-2.5 px-3 text-xs font-bold text-theme-secondary uppercase tracking-wider">Chave</th>
                            <th class="py-2.5 px-3 text-xs font-bold text-theme-secondary uppercase tracking-wider">Valor</th>
                            <th class="py-2.5 px-3 text-xs font-bold text-theme-secondary uppercase tracking-wider text-right w-24">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-theme-subtle">
                        @forelse($settingsList as $setting)
                            <tr class="hover:bg-theme-card/30 transition-colors">
                                @if($editingSetting && $editingSetting->id === $setting->id)
                                    <td class="py-3 px-3 font-mono text-xs text-theme-secondary">{{ $setting->key }}</td>
                                    <td class="py-3 px-3">
                                        <input type="text" wire:model="editSettingValue" class="w-full bg-theme-input border border-blue-500/50 px-2 py-1 rounded-lg text-xs text-theme font-mono focus:outline-none" />
                                    </td>
                                    <td class="py-3 px-3 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <button wire:click="saveSetting" class="bg-emerald-600 hover:bg-emerald-500 text-white px-2 py-1 rounded-lg text-xs font-semibold">Guardar</button>
                                            <button wire:click="cancelEditSetting" class="text-xs text-theme-muted hover:text-theme px-2">Cancelar</button>
                                        </div>
                                    </td>
                                @else
                                    <td class="py-3 px-3 font-mono text-xs text-theme font-bold">{{ $setting->key }}</td>
                                    <td class="py-3 px-3 font-mono text-xs text-theme-secondary truncate max-w-xs">{{ $setting->value }}</td>
                                    <td class="py-3 px-3 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <button wire:click="editSetting({{ $setting->id }})" class="text-xs text-blue-400 hover:text-blue-300 px-2 py-1">Editar</button>
                                            <button wire:click="deleteSetting({{ $setting->id }})" wire:confirm="Eliminar definição '{{ $setting->key }}'?" class="text-xs text-rose-400 hover:text-rose-300 px-2 py-1">Eliminar</button>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-8 text-center text-sm text-theme-muted">Nenhuma definição configurada. Cria a primeira abaixo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Add new setting --}}
            <div class="pt-4 border-t border-theme-subtle space-y-3">
                <h3 class="text-xs font-bold text-theme-secondary uppercase tracking-wider">Adicionar Nova Definição</h3>
                <form wire:submit.prevent="addSetting" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                    <div class="space-y-1">
                        <label for="new-key" class="text-xs text-theme-muted">Chave</label>
                        <input id="new-key" type="text" wire:model="newSettingKey" placeholder="ex: site.maintenance_mode" class="w-full bg-theme-input border border-theme-subtle px-3 py-2 rounded-xl text-sm text-theme font-mono focus:outline-none focus:border-blue-500/50" />
                        @error('newSettingKey') <span class="text-xs text-rose-400 block mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-1">
                        <label for="new-value" class="text-xs text-theme-muted">Valor</label>
                        <input id="new-value" type="text" wire:model="newSettingValue" placeholder="ex: true" class="w-full bg-theme-input border border-theme-subtle px-3 py-2 rounded-xl text-sm text-theme font-mono focus:outline-none focus:border-blue-500/50" />
                        @error('newSettingValue') <span class="text-xs text-rose-400 block mt-1">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 active:scale-95 text-white font-bold py-2.5 rounded-xl text-sm transition-colors">Adicionar Definição</button>
                </form>
            </div>
        </div>

        {{-- Suggested settings --}}
        <div class="glass-card p-5 rounded-2xl space-y-3">
            <h2 class="text-sm font-bold text-theme uppercase tracking-wider flex items-center gap-1.5">
                <span>💡</span> Definições Sugeridas
            </h2>
            <p class="text-xs text-theme-muted">Exemplos de definições que podes criar para controlar o comportamento do site:</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <div class="bg-theme-card border border-theme-subtle p-3 rounded-xl text-xs">
                    <span class="font-bold text-theme block">site.maintenance_mode</span>
                    <span class="text-theme-muted">Colocar o site em modo de manutenção (true/false)</span>
                </div>
                <div class="bg-theme-card border border-theme-subtle p-3 rounded-xl text-xs">
                    <span class="font-bold text-theme block">predictions.enabled</span>
                    <span class="text-theme-muted">Ativar/desativar previsões automáticas (true/false)</span>
                </div>
                <div class="bg-theme-card border border-theme-subtle p-3 rounded-xl text-xs">
                    <span class="font-bold text-theme block">community.reports.enabled</span>
                    <span class="text-theme-muted">Permitir confirmações da comunidade (true/false)</span>
                </div>
                <div class="bg-theme-card border border-theme-subtle p-3 rounded-xl text-xs">
                    <span class="font-bold text-theme block">sync.ipma.interval</span>
                    <span class="text-theme-muted">Intervalo de sincronização IPMA em minutos</span>
                </div>
                <div class="bg-theme-card border border-theme-subtle p-3 rounded-xl text-xs">
                    <span class="font-bold text-theme block">sync.infoagua.interval</span>
                    <span class="text-theme-muted">Intervalo de sincronização InfoÁgua em minutos</span>
                </div>
                <div class="bg-theme-card border border-theme-subtle p-3 rounded-xl text-xs">
                    <span class="font-bold text-theme block">default.flag</span>
                    <span class="text-theme-muted">Bandeira padrão quando não há dados (ex: gray)</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════ TAB 6: SISTEMA ════════════════════════════════════════ --}}
    <div x-show="activeTab === 'sistema'" x-cloak class="space-y-6">

        {{-- System Info --}}
        <div class="glass-card p-5 rounded-2xl space-y-4">
            <h2 class="text-lg font-bold text-theme flex items-center gap-1.5">
                <span>🖥️</span> Informação do Sistema
            </h2>
            <p class="text-xs text-theme-muted">Versões e configuração atual do ambiente de execução.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="bg-theme-card border border-theme-subtle p-3 rounded-xl">
                    <span class="text-xs text-theme-muted block">PHP Version</span>
                    <span class="text-sm font-bold text-theme">{{ $systemInfo['php_version'] }}</span>
                </div>
                <div class="bg-theme-card border border-theme-subtle p-3 rounded-xl">
                    <span class="text-xs text-theme-muted block">Laravel Version</span>
                    <span class="text-sm font-bold text-theme">{{ $systemInfo['laravel_version'] }}</span>
                </div>
                <div class="bg-theme-card border border-theme-subtle p-3 rounded-xl">
                    <span class="text-xs text-theme-muted block">Ambiente</span>
                    <span class="text-sm font-bold text-theme uppercase">{{ $systemInfo['environment'] }}</span>
                </div>
                <div class="bg-theme-card border border-theme-subtle p-3 rounded-xl">
                    <span class="text-xs text-theme-muted block">Modo Debug</span>
                    <span class="text-sm font-bold {{ $systemInfo['debug_mode'] ? 'text-amber-400' : 'text-emerald-400' }}">{{ $systemInfo['debug_mode'] ? 'Ativado' : 'Desativado' }}</span>
                </div>
                <div class="bg-theme-card border border-theme-subtle p-3 rounded-xl">
                    <span class="text-xs text-theme-muted block">Base de Dados</span>
                    <span class="text-sm font-bold text-theme uppercase">{{ $systemInfo['db_driver'] }}</span>
                </div>
                <div class="bg-theme-card border border-theme-subtle p-3 rounded-xl">
                    <span class="text-xs text-theme-muted block">Tarefas na Fila</span>
                    <span class="text-sm font-bold text-amber-400">{{ $systemInfo['queue_size'] >= 0 ? $systemInfo['queue_size'] : 'N/A' }}</span>
                </div>
                <div class="bg-theme-card border border-theme-subtle p-3 rounded-xl">
                    <span class="text-xs text-theme-muted block">Tarefas Falhadas</span>
                    <span class="text-sm font-bold text-rose-400">{{ $systemInfo['failed_jobs'] >= 0 ? $systemInfo['failed_jobs'] : 'N/A' }}</span>
                </div>
                <div class="bg-theme-card border border-theme-subtle p-3 rounded-xl">
                    <span class="text-xs text-theme-muted block">Admin Count</span>
                    <span class="text-sm font-bold text-theme">{{ $adminUsers }}</span>
                </div>
            </div>
        </div>

        {{-- Cache Management --}}
        <div class="glass-card p-5 rounded-2xl space-y-4">
            <h2 class="text-lg font-bold text-theme flex items-center gap-1.5">
                <span>🧹</span> Gestão de Cache
            </h2>
            <p class="text-xs text-theme-muted">Limpar vários tipos de cache. Útil após alterações a configurações, views, ou para resolver problemas de atualização de dados.</p>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                <button wire:click="clearCache" wire:loading.attr="disabled" class="bg-slate-600 hover:bg-slate-500 active:scale-95 text-white font-bold py-3 rounded-xl text-xs transition-all disabled:opacity-50 touch-target flex items-center justify-center gap-1.5">
                    <span wire:loading wire:target="clearCache" class="animate-spin h-3 w-3 border-2 border-white border-t-transparent rounded-full"></span>
                    <span>🧹 Cache App</span>
                </button>
                <button wire:click="clearViewCache" wire:loading.attr="disabled" class="bg-slate-600 hover:bg-slate-500 active:scale-95 text-white font-bold py-3 rounded-xl text-xs transition-all disabled:opacity-50 touch-target flex items-center justify-center gap-1.5">
                    <span wire:loading wire:target="clearViewCache" class="animate-spin h-3 w-3 border-2 border-white border-t-transparent rounded-full"></span>
                    <span>📄 Cache Views</span>
                </button>
                <button wire:click="clearConfigCache" wire:loading.attr="disabled" class="bg-slate-600 hover:bg-slate-500 active:scale-95 text-white font-bold py-3 rounded-xl text-xs transition-all disabled:opacity-50 touch-target flex items-center justify-center gap-1.5">
                    <span wire:loading wire:target="clearConfigCache" class="animate-spin h-3 w-3 border-2 border-white border-t-transparent rounded-full"></span>
                    <span>⚙️ Cache Config</span>
                </button>
                <button wire:click="clearAllCache" wire:loading.attr="disabled" wire:confirm="Limpar toda a cache (optimize:clear)? Isto pode tornar o site temporariamente mais lento." class="bg-rose-600 hover:bg-rose-500 active:scale-95 text-white font-bold py-3 rounded-xl text-xs transition-all disabled:opacity-50 touch-target flex items-center justify-center gap-1.5">
                    <span wire:loading wire:target="clearAllCache" class="animate-spin h-3 w-3 border-2 border-white border-t-transparent rounded-full"></span>
                    <span>🗑️ Limpar Tudo</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Pagination styles (Livewire default) --}}
    <style>
        [x-cloak] { display: none !important; }
    </style>
</div>
