<div class="space-y-6">
    @section('title', 'Rankings CheckPraia - Tabela de Liderança')

    <div class="glass-card p-5 sm:p-6 rounded-2xl border border-theme-medium flex flex-col md:flex-row gap-4 justify-between items-start md:items-center">
        <div class="w-full md:w-auto">
            <h1 class="text-xl sm:text-2xl font-bold text-theme tracking-tight">Tabela de Liderança Nacional</h1>
            <p class="text-xs text-theme-secondary mt-1">Ganha pontos confirmando bandeiras nas praias vigiadas e convidadando amigos.</p>
        </div>

        <!-- Filter tabs and district filter -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
            <!-- District filter -->
            <label for="district-filter" class="sr-only">Filtrar por distrito</label>
            <select id="district-filter" wire:model.live="district" class="glass-input px-3 py-2.5 rounded-xl text-sm w-full sm:w-auto">
                <option value="">Todos os Distritos</option>
                @foreach($districts as $d)
                    <option value="{{ $d }}">{{ $d }}</option>
                @endforeach
            </select>

            <!-- Leaderboard scope tabs -->
            <div class="bg-theme-card p-1 rounded-xl border border-theme-subtle flex gap-1 text-sm overflow-x-auto scrollbar-none" role="tablist" aria-label="Período do ranking">
                <button wire:click="$set('type', 'general')" role="tab" aria-selected="{{ $type === 'general' ? 'true' : 'false' }}" class="px-3 py-1.5 rounded-lg transition-all whitespace-nowrap {{ $type === 'general' ? 'bg-blue-600 text-white font-bold' : 'text-theme-secondary hover:text-theme' }}">
                    Geral
                </button>
                <button wire:click="$set('type', 'monthly')" role="tab" aria-selected="{{ $type === 'monthly' ? 'true' : 'false' }}" class="px-3 py-1.5 rounded-lg transition-all whitespace-nowrap {{ $type === 'monthly' ? 'bg-blue-600 text-white font-bold' : 'text-theme-secondary hover:text-theme' }}">
                    Mensal
                </button>
                <button wire:click="$set('type', 'weekly')" role="tab" aria-selected="{{ $type === 'weekly' ? 'true' : 'false' }}" class="px-3 py-1.5 rounded-lg transition-all whitespace-nowrap {{ $type === 'weekly' ? 'bg-blue-600 text-white font-bold' : 'text-theme-secondary hover:text-theme' }}">
                    Semanal
                </button>
                <button wire:click="$set('type', 'daily')" role="tab" aria-selected="{{ $type === 'daily' ? 'true' : 'false' }}" class="px-3 py-1.5 rounded-lg transition-all whitespace-nowrap {{ $type === 'daily' ? 'bg-blue-600 text-white font-bold' : 'text-theme-secondary hover:text-theme' }}">
                    Diário
                </button>
            </div>
        </div>
    </div>

    <!-- Leaderboard - Mobile Cards (hidden on md+) -->
    <div class="md:hidden space-y-3">
        @forelse($rankingsList as $index => $userItem)
            @php
                $position = $index + 1;
                $badge = match($position) {
                    1 => '🥇',
                    2 => '🥈',
                    3 => '🥉',
                    default => null
                };
                $isTop3 = $position <= 3;
            @endphp
            <div class="glass-card rounded-2xl border border-theme-medium px-4 py-3 flex items-center gap-3 {{ $isTop3 ? 'bg-blue-500/5' : '' }}">
                <div class="w-8 text-center font-bold shrink-0">
                    @if($isTop3)
                        <span class="text-lg">{{ $badge }}</span>
                    @else
                        <span class="text-theme-secondary text-xs">{{ $position }}</span>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-theme text-sm truncate">
                        {{ $userItem->username }}
                    </div>
                    <div class="text-theme-secondary text-xs mt-0.5">
                        {{ $userItem->accepted_confirmations_count }} aceites
                    </div>
                </div>
                @if($userItem->accepted_confirmations_count >= 50 && ($userItem->accepted_confirmations_count / max(1, $userItem->confirmations_count)) >= 0.9)
                    <span class="text-[11px] uppercase tracking-wide px-1.5 py-0.5 bg-teal-500/10 text-teal-300 border border-teal-500/20 rounded font-black shrink-0">
                        Voto Reforçado ⚡
                    </span>
                @endif
                <div class="text-right shrink-0">
                    <div class="font-extrabold text-blue-400 text-sm">{{ $userItem->rank_score }}</div>
                    <div class="text-xs text-theme-muted font-normal uppercase tracking-wide">pts</div>
                </div>
            </div>
        @empty
            <div class="glass-card rounded-2xl border border-theme-medium px-4 py-8 text-center text-theme-muted text-sm">
                Nenhum utilizador registou atividade neste período.
            </div>
        @endforelse
    </div>

    <!-- Leaderboard Table (hidden on mobile, shown on md+) -->
    <div class="hidden md:block glass-card rounded-2xl border border-theme-medium overflow-x-auto">
        <table class="w-full text-left border-collapse" aria-label="Tabela de classificação">
            <caption class="sr-only">Classificação dos utilizadores por pontuação</caption>
            <thead>
                <tr class="border-b border-theme-subtle text-theme-secondary text-xs sm:text-sm font-bold uppercase tracking-wider">
                    <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 w-16 sm:w-20 text-center">Posição</th>
                    <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4">Utilizador</th>
                    <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-center">Confirmações</th>
                    <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-right">Pontuação</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-theme-subtle text-sm text-theme">
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
                    <tr class="transition-colors {{ $isTop3 ? 'bg-blue-500/5' : '' }}">
                        <th scope="row" class="px-3 sm:px-6 py-3 sm:py-4 text-center font-bold">
                            @if($isTop3)
                                <span class="text-xl" aria-label="Posição {{ $position }}">{{ $badge }}</span>
                            @else
                                <span class="text-theme-secondary text-xs">{{ $badge }}</span>
                            @endif
                        </th>
                        <td class="px-3 sm:px-6 py-3 sm:py-4 font-semibold text-theme text-xs sm:text-sm">
                            {{ $userItem->username }}
                            @if($userItem->accepted_confirmations_count >= 50 && ($userItem->accepted_confirmations_count / max(1, $userItem->confirmations_count)) >= 0.9)
                                <span class="ml-2 text-xs uppercase tracking-wide px-1.5 py-0.5 bg-teal-500/10 text-teal-300 border border-teal-500/20 rounded font-black">
                                    Voto Reforçado <span aria-hidden="true">⚡</span>
                                </span>
                            @endif
                        </td>
                        <td class="px-3 sm:px-6 py-3 sm:py-4 text-center text-theme-secondary text-xs sm:text-sm font-medium">
                            {{ $userItem->accepted_confirmations_count }} aceites
                        </td>
                        <td class="px-3 sm:px-6 py-3 sm:py-4 text-right font-extrabold text-blue-400 text-sm sm:text-base">
                            {{ $userItem->rank_score }} <span class="text-xs text-theme-muted font-normal uppercase tracking-wide">pts</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-3 sm:px-6 py-8 sm:py-12 text-center text-theme-muted text-sm">
                            Nenhum utilizador registou atividade neste período.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
