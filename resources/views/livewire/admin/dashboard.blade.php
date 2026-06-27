<div class="space-y-8">
    @section('title', 'Painel de Administração - CheckPraia')

    <div class="glass-card p-6 rounded-2xl border border-white/10 flex justify-between items-center bg-teal-950/10 border-teal-500/20">
        <div>
            <h1 class="text-2xl font-bold text-teal-300 tracking-tight">Painel de Backoffice</h1>
            <p class="text-xs text-slate-400">Monitoriza as métricas do CheckPraia, gere utilizadores e agenda campanhas.</p>
        </div>
        <span class="text-xs font-mono bg-slate-900 border border-slate-700 px-3 py-1.5 rounded-lg text-slate-400">Acesso Administrativo</span>
    </div>

    <!-- Backoffice Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="glass-card p-4 rounded-xl text-center">
            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider block">Utilizadores Registados</span>
            <span class="text-2xl font-black text-white block mt-1">{{ $totalUsers }}</span>
        </div>
        <div class="glass-card p-4 rounded-xl text-center">
            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider block">Confirmações Hoje</span>
            <span class="text-2xl font-black text-emerald-400 block mt-1">{{ $reportsToday }}</span>
        </div>
        <div class="glass-card p-4 rounded-xl text-center">
            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider block">Previsões Recalculadas</span>
            <span class="text-2xl font-black text-blue-400 block mt-1">{{ $totalPredictions }}</span>
        </div>
        <div class="glass-card p-4 rounded-xl text-center">
            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider block">Avisos Ativos</span>
            <span class="text-2xl font-black text-rose-400 block mt-1">{{ $activeAlertsCount }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Left: User Management Panel (Span 7) -->
        <div class="lg:col-span-7 space-y-6">
            <div class="glass-card p-6 rounded-3xl space-y-4">
                <div class="flex justify-between items-center gap-4 flex-wrap">
                    <h3 class="text-lg font-bold text-white">Gestão de Utilizadores</h3>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="searchUser" 
                        placeholder="Pesquisar por username, nome ou email..." 
                        class="glass-input px-3.5 py-1.5 rounded-xl text-xs w-full sm:w-64"
                    />
                </div>

                @if(session()->has('user_action'))
                    <div class="p-2.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs rounded-xl">
                        {{ session('user_action') }}
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs text-slate-200">
                        <thead>
                            <tr class="border-b border-white/5 text-slate-400 font-bold uppercase">
                                <th class="py-2.5">Nome / Username</th>
                                <th class="py-2.5">Email</th>
                                <th class="py-2.5 text-center">Pontos</th>
                                <th class="py-2.5 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @foreach($usersList as $usr)
                                <tr class="hover:bg-white/[0.01]">
                                    <td class="py-3 font-semibold text-white">
                                        {{ $usr->name }}
                                        <span class="block text-[10px] text-slate-500">@ {{ $usr->username }}</span>
                                    </td>
                                    <td class="py-3 text-slate-400">{{ $usr->email }}</td>
                                    <td class="py-3 text-center font-bold text-blue-400">{{ $usr->score }}</td>
                                    <td class="py-3 text-right space-x-1">
                                        <button wire:click="selectUser({{ $usr->id }})" class="bg-blue-600/10 hover:bg-blue-600 text-blue-400 hover:text-white px-2 py-1 rounded border border-blue-500/20 hover:border-blue-500 transition-colors">
                                            Ajustar
                                        </button>
                                        <button wire:click="toggleSuspension({{ $usr->id }})" class="px-2 py-1 rounded transition-colors {{ $usr->is_suspended ? 'bg-emerald-600/10 hover:bg-emerald-600 text-emerald-400 hover:text-white border border-emerald-500/20 hover:border-emerald-500' : 'bg-rose-600/10 hover:bg-rose-600 text-rose-400 hover:text-white border border-rose-500/20 hover:border-rose-500' }}">
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
                        <button wire:click="$set('selectedUser', null)" class="text-xs text-slate-400 hover:text-white">Cancelar</button>
                    </div>

                    @if(session()->has('adjust_success'))
                        <div class="p-2.5 bg-emerald-500/20 border border-emerald-500/30 text-emerald-200 text-xs rounded-xl">
                            {{ session('adjust_success') }}
                        </div>
                    @endif

                    <form wire:submit.prevent="adjustScore" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                        <div class="space-y-1">
                            <label class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Nova Pontuação</label>
                            <input type="number" wire:model="adjustmentPoints" class="w-full glass-input px-3 py-2 rounded-xl text-xs" />
                            @error('adjustmentPoints') <span class="text-[9px] text-rose-400 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Justificação</label>
                            <input type="text" wire:model="justification" placeholder="Ex: Correção de falsa penalização..." class="w-full glass-input px-3 py-2 rounded-xl text-xs" />
                            @error('justification') <span class="text-[9px] text-rose-400 block">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 rounded-xl text-xs transition-colors">
                            Guardar Ajuste
                        </button>
                    </form>
                </div>
            @endif

            <!-- Adjustments audit history log -->
            <div class="glass-card p-6 rounded-3xl space-y-4">
                <h3 class="text-lg font-bold text-white">Histórico de Ajustes Administrativos</h3>
                <div class="space-y-2">
                    @forelse($adjustmentsList as $adj)
                        <div class="bg-white/5 border border-white/5 p-3 rounded-2xl flex flex-col sm:flex-row justify-between sm:items-center text-xs gap-2">
                            <div>
                                <span class="font-bold text-white block">Ajuste de {{ $adj->difference > 0 ? '+' : '' }}{{ $adj->difference }} pontos</span>
                                <span class="text-[10px] text-slate-400 block">Utilizador: @ {{ $adj->target->username }} &bull; Justificação: "{{ $adj->justification }}"</span>
                            </div>
                            <div class="text-[9px] text-slate-500 sm:text-right">
                                <span>Por: @ {{ $adj->admin->username }}</span>
                                <span class="block">{{ $adj->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 text-center py-4">Sem registos de ajustes na auditoria de backoffice.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right: Ad Placement Schedulers (Span 5) -->
        <div class="lg:col-span-5 space-y-6">
            <div class="glass-card p-6 rounded-3xl space-y-4">
                <h3 class="text-lg font-bold text-white">Agendar Campanha de Publicidade</h3>
                
                @if(session()->has('campaign_success'))
                    <div class="p-2.5 bg-emerald-500/20 border border-emerald-500/30 text-emerald-200 text-xs rounded-xl font-medium">
                        ✔️ {{ session('campaign_success') }}
                    </div>
                @endif

                <form wire:submit.prevent="createCampaign" class="space-y-4 text-xs">
                    <div class="space-y-1">
                        <label class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Nome do Parceiro/Cliente</label>
                        <input type="text" wire:model="clientName" placeholder="Ex: Escola de Surf da Barra" class="w-full glass-input px-3.5 py-2.5 rounded-xl" />
                        @error('clientName') <span class="text-[9px] text-rose-400 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Título de Anúncio</label>
                        <input type="text" wire:model="campaignTitle" placeholder="Ex: Desconto de 15% nas primeiras 3 aulas!" class="w-full glass-input px-3.5 py-2.5 rounded-xl" />
                        @error('campaignTitle') <span class="text-[9px] text-rose-400 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Endereço de Link de Destino</label>
                        <input type="url" wire:model="campaignLink" placeholder="Ex: https://escolasurfbarra.pt" class="w-full glass-input px-3.5 py-2.5 rounded-xl" />
                        @error('campaignLink') <span class="text-[9px] text-rose-400 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Data de Início</label>
                            <input type="date" wire:model="campaignStartsAt" class="w-full glass-input px-3.5 py-2.5 rounded-xl bg-slate-900" />
                            @error('campaignStartsAt') <span class="text-[9px] text-rose-400 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Data de Fim</label>
                            <input type="date" wire:model="campaignEndsAt" class="w-full glass-input px-3.5 py-2.5 rounded-xl bg-slate-900" />
                            @error('campaignEndsAt') <span class="text-[9px] text-rose-400 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Localização do Banner</label>
                            <select wire:model="placementType" class="w-full glass-input px-3.5 py-2.5 rounded-xl bg-slate-900 border-white/12">
                                <option value="home">Página Inicial</option>
                                <option value="beach">Ficha de Praia</option>
                                <option value="list">Listagem de Resultados</option>
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Praia Específica (Opcional)</label>
                            <select wire:model="campaignBeachId" class="w-full glass-input px-3.5 py-2.5 rounded-xl bg-slate-900 border-white/12">
                                <option value="">Nenhuma (Global/Regional)</option>
                                @foreach($beachesList as $bc)
                                    <option value="{{ $bc->id }}">{{ $bc->name }}</option>
                                @endforeach
                            </select>
                            @error('campaignBeachId') <span class="text-[9px] text-rose-400 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-xl transition-colors">
                        Agendar e Ativar Campanha
                    </button>
                </form>
            </div>

            <!-- Operations details logs status -->
            <div class="glass-card p-6 rounded-3xl space-y-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider">Estado Operacional de APIs Externas</h3>
                
                <div class="space-y-3 text-xs">
                    <div class="flex justify-between items-center p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300">
                        <span>🌤️ IPMA Meteo & Swell Forecast API</span>
                        <span class="font-extrabold text-[10px] uppercase">Operacional</span>
                    </div>

                    <div class="flex justify-between items-center p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300">
                        <span>💧 InfoÁgua Balnear Quality API</span>
                        <span class="font-extrabold text-[10px] uppercase">Operacional</span>
                    </div>

                    <div class="flex justify-between items-center p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300">
                        <span>🏨 TripAdvisor Location Partner API</span>
                        <span class="font-extrabold text-[10px] uppercase">Operacional (Cache)</span>
                    </div>

                    <div class="flex justify-between items-center p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300">
                        <span>🍴 TheFork Booking API Wrapper</span>
                        <span class="font-extrabold text-[10px] uppercase">Operacional (Cache)</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
