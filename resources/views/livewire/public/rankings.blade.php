<div class="space-y-6">
    @section('title', __('rankings.page_title'))
    @section('meta_description', __('rankings.page_description'))

    <div class="glass-card p-5 sm:p-6 rounded-2xl border border-theme-medium flex flex-col md:flex-row gap-4 justify-between items-start md:items-center animate-fade-in-up">
        <div class="w-full md:w-auto">
            <h1 class="text-xl sm:text-2xl font-bold text-theme tracking-tight">{{ __('rankings.title') }}</h1>
            <p class="text-xs text-theme-secondary mt-1">{{ __('rankings.subtitle') }}</p>
            @auth
                @if($currentUserPosition)
                    <div x-data="rankingShareHandler()"
                         data-position="{{ $currentUserPosition }}"
                         data-score="{{ $currentUserScore }}"
                         data-username="{{ auth()->user()->name }}"
                         class="mt-3 flex flex-wrap items-center gap-2">
                        <button @click="share()" class="inline-flex items-center gap-1.5 text-xs font-semibold bg-blue-600/10 hover:bg-blue-600/20 text-blue-400 border border-blue-500/20 hover:border-blue-500/40 px-3 py-1.5 rounded-xl transition-all active:scale-95">
                            <span aria-hidden="true">📢</span> {{ __('rankings.share_ranking') }}
                        </button>
                        <button @click="toggleCard()" class="inline-flex items-center gap-1.5 text-xs font-semibold bg-theme-card hover:bg-white/5 text-theme-secondary border border-theme-medium px-3 py-1.5 rounded-xl transition-all active:scale-95">
                            <span aria-hidden="true">🖼️</span> {{ __('profile.share_card_download') }}
                        </button>
                        <div x-show="showCard" x-cloak x-transition class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4" @click.self="toggleCard()">
                            <div class="bg-theme-card rounded-2xl border border-theme-medium shadow-2xl p-6 max-w-lg w-full space-y-4">
                                <canvas id="share-card-preview" class="w-full rounded-xl border border-theme-subtle"></canvas>
                                <div class="flex gap-3">
                                    <button @click="downloadCard()" class="flex-1 bg-blue-600 hover:bg-blue-500 text-white font-bold py-2.5 rounded-xl text-sm transition-all active:scale-95">
                                        ⬇️ {{ __('profile.share_card_download') }}
                                    </button>
                                    <button @click="toggleCard()" class="px-4 bg-theme-card text-theme-secondary border border-theme-medium py-2.5 rounded-xl text-sm transition-all active:scale-95">
                                        {{ __('common.filter_all') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <p class="mt-3 text-xs text-theme-muted italic">{{ __('rankings.share_rank_not_found') }}</p>
                @endif
            @endauth
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
            <label for="district-filter" class="sr-only">{{ __('rankings.filter_district') }}</label>
            <select id="district-filter" wire:model.live="district" class="glass-input px-3 py-2.5 rounded-xl text-sm w-full sm:w-auto">
                <option value="">{{ __('rankings.all_districts') }}</option>
                @foreach($districts as $d)
                    <option value="{{ $d }}">{{ $d }}</option>
                @endforeach
            </select>

            <div class="bg-theme-card p-1 rounded-xl border border-theme-subtle flex gap-1 text-sm overflow-x-auto scrollbar-none" role="tablist" aria-label="{{ __('rankings.period') }}">
                <button wire:click="$set('type', 'general')" role="tab" aria-selected="{{ $type === 'general' ? 'true' : 'false' }}" class="px-3 py-1.5 rounded-lg transition-all whitespace-nowrap active:scale-95 {{ $type === 'general' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'text-theme-secondary hover:text-theme' }}">
                    {{ __('rankings.general') }}
                </button>
                <button wire:click="$set('type', 'monthly')" role="tab" aria-selected="{{ $type === 'monthly' ? 'true' : 'false' }}" class="px-3 py-1.5 rounded-lg transition-all whitespace-nowrap active:scale-95 {{ $type === 'monthly' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'text-theme-secondary hover:text-theme' }}">
                    {{ __('rankings.monthly') }}
                </button>
                <button wire:click="$set('type', 'weekly')" role="tab" aria-selected="{{ $type === 'weekly' ? 'true' : 'false' }}" class="px-3 py-1.5 rounded-lg transition-all whitespace-nowrap active:scale-95 {{ $type === 'weekly' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'text-theme-secondary hover:text-theme' }}">
                    {{ __('rankings.weekly') }}
                </button>
                <button wire:click="$set('type', 'daily')" role="tab" aria-selected="{{ $type === 'daily' ? 'true' : 'false' }}" class="px-3 py-1.5 rounded-lg transition-all whitespace-nowrap active:scale-95 {{ $type === 'daily' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'text-theme-secondary hover:text-theme' }}">
                    {{ __('rankings.daily') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Cards -->
    <div class="md:hidden space-y-3" data-animate-stagger="0.05">
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
            <div class="glass-card rounded-2xl border border-theme-medium px-4 py-3 flex items-center gap-3 card-lift {{ $isTop3 ? 'bg-blue-500/5 border-blue-500/20' : '' }}">
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
                        {{ __('rankings.confirmations_count', ['count' => $userItem->accepted_confirmations_count]) }}
                    </div>
                </div>
                @if($userItem->accepted_confirmations_count >= 50 && ($userItem->accepted_confirmations_count / max(1, $userItem->confirmations_count)) >= 0.9)
                    <span class="text-[11px] uppercase tracking-wide px-1.5 py-0.5 bg-teal-500/10 text-teal-300 border border-teal-500/20 rounded font-black shrink-0">
                        {{ __('rankings.boosted_vote') }} ⚡
                    </span>
                @endif
                <div class="text-right shrink-0">
                    <div class="font-extrabold text-blue-400 text-sm">{{ $userItem->rank_score }}</div>
                    <div class="text-xs text-theme-muted font-normal uppercase tracking-wide">{{ __('rankings.pts') }}</div>
                </div>
            </div>
        @empty
            <div class="glass-card rounded-2xl border border-theme-medium px-4 py-8 text-center text-theme-muted text-sm">
                {{ __('rankings.no_activity') }}
            </div>
        @endforelse
    </div>

    <!-- Desktop Table -->
    <div class="hidden md:block glass-card rounded-2xl border border-theme-medium overflow-x-auto animate-fade-in-up">
        <table class="w-full text-left border-collapse" aria-label="{{ __('rankings.table_aria') }}">
            <caption class="sr-only">{{ __('rankings.table_caption') }}</caption>
            <thead>
                <tr class="border-b border-theme-subtle text-theme-secondary text-xs sm:text-sm font-bold uppercase tracking-wider">
                    <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 w-16 sm:w-20 text-center">{{ __('rankings.position') }}</th>
                    <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4">{{ __('rankings.user') }}</th>
                    <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-center">{{ __('rankings.confirmations') }}</th>
                    <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-right">{{ __('rankings.score') }}</th>
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
                    <tr class="transition-colors hover:bg-white/[0.02] {{ $isTop3 ? 'bg-blue-500/5' : '' }}">
                        <th scope="row" class="px-3 sm:px-6 py-3 sm:py-4 text-center font-bold">
                            @if($isTop3)
                                <span class="text-xl" aria-label="{{ __('rankings.position_aria', ['position' => $position]) }}">{{ $badge }}</span>
                            @else
                                <span class="text-theme-secondary text-xs">{{ $badge }}</span>
                            @endif
                        </th>
                        <td class="px-3 sm:px-6 py-3 sm:py-4 font-semibold text-theme text-xs sm:text-sm">
                            {{ $userItem->username }}
                            @if($userItem->accepted_confirmations_count >= 50 && ($userItem->accepted_confirmations_count / max(1, $userItem->confirmations_count)) >= 0.9)
                                <span class="ml-2 text-xs uppercase tracking-wide px-1.5 py-0.5 bg-teal-500/10 text-teal-300 border border-teal-500/20 rounded font-black">
                                    {{ __('rankings.boosted_vote') }} <span aria-hidden="true">⚡</span>
                                </span>
                            @endif
                        </td>
                        <td class="px-3 sm:px-6 py-3 sm:py-4 text-center text-theme-secondary text-xs sm:text-sm font-medium">
                            {{ __('rankings.confirmations_count', ['count' => $userItem->accepted_confirmations_count]) }}
                        </td>
                        <td class="px-3 sm:px-6 py-3 sm:py-4 text-right font-extrabold text-blue-400 text-sm sm:text-base">
                            {{ $userItem->rank_score }} <span class="text-xs text-theme-muted font-normal uppercase tracking-wide">{{ __('rankings.pts') }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-3 sm:px-6 py-8 sm:py-12 text-center text-theme-muted text-sm">
                            {{ __('rankings.no_activity') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <x-ads.slot slot="rankings_bottom" />
    </div>

    @if($hasMore)
        <div class="flex justify-center pt-2 pb-6">
            <button wire:click="loadMore" class="px-6 py-2.5 rounded-xl bg-blue-600/10 border border-blue-500/20 text-blue-400 font-bold text-sm hover:bg-blue-600/20 active:scale-95 transition-all">
                {{ __('rankings.load_more') }}
            </button>
        </div>
    @endif
</div>
