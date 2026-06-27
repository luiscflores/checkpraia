<div class="space-y-6">
    @section('title', 'Rankings CheckPraia - Tabela de Liderança')

    <div class="glass-card p-6 rounded-2xl border border-white/10 flex flex-col md:flex-row gap-4 justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Tabela de Liderança Nacional</h1>
            <p class="text-xs text-slate-400">Ganha pontos confirmando bandeiras nas praias vigiadas e convidadando amigos.</p>
        </div>

        <!-- Filter tabs and district filter -->
        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <!-- District filter -->
            <select wire:model.live="district" class="glass-input px-3 py-2 rounded-xl text-xs bg-slate-900 border-white/12">
                <option value="">Todos os Distritos</option>
                <option value="Aveiro">Aveiro</option>
                <option value="Beja">Beja</option>
                <option value="Braga">Braga</option>
                <option value="Coimbra">Coimbra</option>
                <option value="Faro">Faro (Algarve)</option>
                <option value="Leiria">Leiria</option>
                <option value="Lisboa">Lisboa</option>
                <option value="Porto">Porto</option>
                <option value="Setúbal">Setúbal</option>
                <option value="Viana do Castelo">Viana do Castelo</option>
                <option value="Madeira">Madeira</option>
                <option value="Açores">Açores</option>
            </select>

            <!-- Leaderboard scope tabs -->
            <div class="bg-slate-800/80 p-1 rounded-xl border border-white/5 flex gap-1 text-xs">
                <button wire:click="$set('type', 'general')" class="px-3 py-1.5 rounded-lg transition-all {{ $type === 'general' ? 'bg-blue-600 text-white font-bold' : 'text-slate-400 hover:text-white' }}">
                    Geral
                </button>
                <button wire:click="$set('type', 'monthly')" class="px-3 py-1.5 rounded-lg transition-all {{ $type === 'monthly' ? 'bg-blue-600 text-white font-bold' : 'text-slate-400 hover:text-white' }}">
                    Mensal
                </button>
                <button wire:click="$set('type', 'weekly')" class="px-3 py-1.5 rounded-lg transition-all {{ $type === 'weekly' ? 'bg-blue-600 text-white font-bold' : 'text-slate-400 hover:text-white' }}">
                    Semanal
                </button>
                <button wire:click="$set('type', 'daily')" class="px-3 py-1.5 rounded-lg transition-all {{ $type === 'daily' ? 'bg-blue-600 text-white font-bold' : 'text-slate-400 hover:text-white' }}">
                    Diário
                </button>
            </div>
        </div>
    </div>

    <!-- Leaderboard Table -->
    <div class="glass-card rounded-2xl border border-white/10 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-white/5 bg-white/[0.02] text-slate-400 text-xs font-bold uppercase tracking-wider">
                    <th class="px-6 py-4 w-20 text-center">Posição</th>
                    <th class="px-6 py-4">Utilizador</th>
                    <th class="px-6 py-4 text-center">Confirmações</th>
                    <th class="px-6 py-4 text-right">Pontuação</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5 text-sm text-slate-200">
                @forelse($rankingsList as $index => $userItem)
                    @php
                        $position = $index + 1;
                        $badge = match($position) {
                            1 => '🥇',
                            2 => '🥈',
                            3 => '🥉',
                            default => $position
                        };
                        $isTop3 = $position <= 3;
                    @endphp
                    <tr class="hover:bg-white/[0.01] transition-colors {{ $isTop3 ? 'bg-blue-500/5' : '' }}">
                        <td class="px-6 py-4 text-center font-bold">
                            @if($isTop3)
                                <span class="text-xl">{{ $badge }}</span>
                            @else
                                <span class="text-slate-400 text-xs">{{ $badge }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-semibold text-white">
                            {{ $userItem->username }}
                            @if($userItem->accepted_confirmations_count >= 50 && ($userItem->accepted_confirmations_count / max(1, $userItem->confirmations_count)) >= 0.9)
                                <span class="ml-2 text-[9px] uppercase tracking-wide px-1.5 py-0.5 bg-teal-500/10 text-teal-300 border border-teal-500/20 rounded font-black">
                                    Voto Reforçado ⚡
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center text-slate-400 text-xs font-medium">
                            {{ $userItem->accepted_confirmations_count }} aceites
                        </td>
                        <td class="px-6 py-4 text-right font-extrabold text-blue-400 text-base">
                            {{ $userItem->rank_score }} <span class="text-[10px] text-slate-500 font-normal uppercase tracking-wide">pts</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-slate-500 text-xs">
                            Nenhum utilizador registou atividade neste período.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
