<?php

use App\Models\Beach;
use App\Models\FlagReport;
use App\Models\ScoreTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use function Livewire\Volt\{state, action, layout, with};

layout('components.layouts.app');

state(['type' => 'general'])->url(except: 'general');
state(['district' => ''])->url(except: '');
state(['search' => ''])->url(except: '');
state(['perPage' => 50]);

state(['showUserModal' => false]);
state(['selectedUser' => null]);
state(['userBeaches' => []]);
state(['userPosition' => null]);
state(['userRankScore' => null]);

$loadMore = action(function () {
    $this->perPage += 50;
});

$openUser = action(function (int $userId, int $position, int $rankScore) {
    $user = User::where('is_suspended', false)
        ->whereNotNull('username')
        ->find($userId);

    if (! $user) {
        return;
    }

    $this->selectedUser = $user;
    $this->userPosition = $position;
    $this->userRankScore = $rankScore;

    // Cache user beach activity per user — 10 min
    $this->userBeaches = Cache::remember("user_beaches:{$userId}", 600, function () use ($userId) {
        return FlagReport::where('user_id', $userId)
            ->where('status', 'confirmed')
            ->join('beaches', 'flag_reports.beach_id', '=', 'beaches.id')
            ->select(
                'beaches.id',
                'beaches.name',
                'beaches.district',
                'beaches.municipality',
                DB::raw('COUNT(*) as confirmations'),
                DB::raw('MAX(flag_reports.reported_at) as last_report')
            )
            ->groupBy('beaches.id', 'beaches.name', 'beaches.district', 'beaches.municipality')
            ->orderBy('confirmations', 'desc')
            ->get()
            ->toArray();
    });

    $this->showUserModal = true;
});

$closeUserModal = action(function () {
    $this->showUserModal = false;
    $this->selectedUser = null;
    $this->userBeaches = [];
});

with(function () {
    // 1. Build base period transaction query if needed
    $periodQuery = null;
    if ($this->type !== 'general') {
        $periodQuery = ScoreTransaction::where('status', 'confirmed')
            ->when($this->type === 'daily', fn ($q) => $q->where('created_at', '>=', now()->startOfDay()))
            ->when($this->type === 'weekly', fn ($q) => $q->where('created_at', '>=', now()->startOfWeek()))
            ->when($this->type === 'monthly', fn ($q) => $q->where('created_at', '>=', now()->startOfMonth()));
    }

    // 2. Fetch eligible users sorted by correct score
    if ($this->type === 'general') {
        $usersQuery = User::where('is_suspended', false)
            ->whereNotNull('username')
            ->select('users.*', DB::raw('score as rank_score'))
            ->orderBy('score', 'desc');
    } else {
        // Aggregate score per user in database and left join
        $usersQuery = User::where('users.is_suspended', false)
            ->whereNotNull('users.username')
            ->leftJoinSub(
                $periodQuery->groupBy('user_id')->select('user_id', DB::raw('SUM(points) as period_score')),
                'scores',
                'users.id',
                '=',
                'scores.user_id'
            )
            ->select('users.*', DB::raw('COALESCE(scores.period_score, 0) as rank_score'))
            ->orderBy('rank_score', 'desc');
    }

    // 3. Apply district filter using optimized database subquery
    if ($this->district) {
        $usersQuery->whereIn('users.id', function ($q) {
            $q->select('user_id')
                ->from('flag_reports')
                ->join('beaches', 'flag_reports.beach_id', '=', 'beaches.id')
                ->where('beaches.district', $this->district)
                ->distinct();
        });
    }

    // 4. Apply search filter
    if ($this->search !== '') {
        $usersQuery->where('username', 'like', '%'.$this->search.'%');
    }

    $cacheKey = 'rankings_users:v2:' . $this->type . ':' . ($this->district ?: '') . ':' . md5($this->search) . ':' . $this->perPage;
    $users = Cache::remember($cacheKey, 300, function () use ($usersQuery) {
        return $usersQuery->take($this->perPage + 1)->get();
    });

    $hasMore = $users->count() > $this->perPage;
    $displayedUsers = $users->take($this->perPage);

    // 5. Districts for filter dropdown — cache 1 hour (nearly immutable data)
    $districts = Cache::remember('rankings_districts', 3600, function () {
        return Beach::whereNotNull('district')
            ->where('district', '!=', '')
            ->select('district')
            ->distinct()
            ->orderBy('district')
            ->pluck('district');
    });

    // 6. Current user's position + confirmed beaches
    $currentUserPosition = null;
    $currentUserScore = null;
    $currentUserBeaches = [];

    if (auth()->check()) {
        $userId = auth()->id();
        if ($this->type === 'general') {
            $currentUserScore = (int) auth()->user()->score;
            $pos = User::where('is_suspended', false)
                ->whereNotNull('username')
                ->where('score', '>', $currentUserScore)
                ->count();
            $currentUserPosition = $pos + 1;
        } else {
            // Calculate current user's period score
            $currentUserScore = (int) (clone $periodQuery)->where('user_id', $userId)->sum('points');

            // Count users with a higher score in the period
            $pos = User::where('is_suspended', false)
                ->whereNotNull('username')
                ->joinSub(
                    (clone $periodQuery)->groupBy('user_id')->select('user_id', DB::raw('SUM(points) as total_points')),
                    'scores',
                    'users.id',
                    '=',
                    'scores.user_id'
                )
                ->where('scores.total_points', '>', $currentUserScore)
                ->count();

            $currentUserPosition = $pos + 1;
        }

        // Fetch confirmed beaches for the share card — cache 10 min per user
        $currentUserBeaches = Cache::remember("user_beaches_share:{$userId}", 600, function () use ($userId) {
            return FlagReport::where('user_id', $userId)
                ->where('status', 'confirmed')
                ->join('beaches', 'flag_reports.beach_id', '=', 'beaches.id')
                ->select('beaches.name', DB::raw('COUNT(*) as count'))
                ->groupBy('beaches.name')
                ->orderBy('count', 'desc')
                ->limit(8)
                ->pluck('count', 'name')
                ->toArray();
        });
    }

    return [
        'rankingsList' => $displayedUsers,
        'districts' => $districts,
        'currentUserPosition' => $currentUserPosition,
        'currentUserScore' => $currentUserScore,
        'currentUserBeaches' => $currentUserBeaches,
        'hasMore' => $hasMore,
    ];
});

?>

<div class="space-y-6" x-data="rankingShareHandler({
     position: @js($currentUserPosition ?? null),
     score: @js($currentUserScore ?? null),
     username: @js(auth()->check() ? (auth()->user->username ?? auth()->user->name ?? '') : ''),
     beaches: @js($currentUserBeaches ?? [])
 })">

    @section('title', __('rankings.page_title'))
    @section('meta_description', __('rankings.page_description'))

    @if(auth()->check() && $currentUserPosition)
    {{-- Share Card Modal - Rebuilt from scratch --}}
    <div
        x-show="showCard"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center bg-black/80 backdrop-blur-md sm:p-4"
        @click.self="toggleCard()"
        role="dialog"
        aria-modal="true"
        aria-label="{{ __('rankings.share_ranking') }}"
    >
        <div
            x-transition:enter="transition ease-out duration-350"
            x-transition:enter-start="opacity-0 translate-y-10 sm:translate-y-0 sm:scale-90"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            class="w-full sm:max-w-sm rounded-t-3xl sm:rounded-3xl overflow-hidden shadow-2xl"
            style="background: linear-gradient(145deg, #0f172a 0%, #0c1e3a 50%, #091428 100%); border: 1px solid rgba(59,130,246,0.2);"
            @click.stop
        >
            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-5 pt-5 pb-3">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest" style="color: #38bdf8;">🏆 {{ __('rankings.share_ranking') }}</p>
                    <h3 class="text-base font-black text-white mt-0.5">{{ __('rankings.share_card_headline') }}</h3>
                </div>
                <button
                    @click="toggleCard()"
                    id="share-card-close-btn"
                    class="w-9 h-9 flex items-center justify-center rounded-full transition-all active:scale-90"
                    style="background: rgba(255,255,255,0.06); color: #94a3b8;"
                    aria-label="Fechar"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Card Preview --}}
            <div class="px-5 pb-3">
                <div class="relative rounded-2xl overflow-hidden" style="box-shadow: 0 0 0 1px rgba(59,130,246,0.15), 0 20px 60px rgba(0,0,0,0.5);">
                    <canvas id="share-card-preview" class="w-full block" style="aspect-ratio: 1/1;"></canvas>
                    {{-- Loading state --}}
                    <div x-show="cardLoading" class="absolute inset-0 flex items-center justify-center" style="background: rgba(9,20,40,0.8);">
                        <div class="w-8 h-8 rounded-full border-2 border-blue-500/30 border-t-blue-500 animate-spin"></div>
                    </div>
                </div>
            </div>

            {{-- Share text preview --}}
            <div class="px-5 pb-3">
                <p class="text-xs text-center leading-relaxed" style="color: #64748b;" x-text="shareText"></p>
            </div>

            {{-- Social Platforms --}}
            <div class="grid grid-cols-4 gap-2 px-5 pb-4">
                <button
                    @click="shareTo('whatsapp')"
                    id="share-whatsapp-btn"
                    class="flex flex-col items-center gap-1.5 py-3 px-1 rounded-2xl transition-all active:scale-90 hover:scale-105"
                    style="background: rgba(37,211,102,0.1); border: 1px solid rgba(37,211,102,0.2);"
                >
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="#25D366"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    <span class="text-[9px] font-bold" style="color: #25D366;">WhatsApp</span>
                </button>
                <button
                    @click="shareTo('instagram')"
                    id="share-instagram-btn"
                    class="flex flex-col items-center gap-1.5 py-3 px-1 rounded-2xl transition-all active:scale-90 hover:scale-105"
                    style="background: rgba(225,48,108,0.1); border: 1px solid rgba(225,48,108,0.2);"
                >
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none"><defs><linearGradient id="ig2" x1="0" y1="24" x2="24" y2="0"><stop offset="0%" stop-color="#833AB4"/><stop offset="50%" stop-color="#E1306C"/><stop offset="100%" stop-color="#F77737"/></linearGradient></defs><rect x="2" y="2" width="20" height="20" rx="5" stroke="url(#ig2)" stroke-width="2"/><circle cx="12" cy="12" r="5" stroke="url(#ig2)" stroke-width="2"/><circle cx="17.5" cy="6.5" r="1.5" fill="url(#ig2)"/></svg>
                    <span class="text-[9px] font-bold" style="background: linear-gradient(to right, #833AB4, #E1306C, #F77737); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Insta</span>
                </button>
                <button
                    @click="shareTo('facebook')"
                    id="share-facebook-btn"
                    class="flex flex-col items-center gap-1.5 py-3 px-1 rounded-2xl transition-all active:scale-90 hover:scale-105"
                    style="background: rgba(24,119,242,0.1); border: 1px solid rgba(24,119,242,0.2);"
                >
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="#1877F2"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    <span class="text-[9px] font-bold" style="color: #1877F2;">Facebook</span>
                </button>
                <button
                    @click="shareTo('x')"
                    id="share-x-btn"
                    class="flex flex-col items-center gap-1.5 py-3 px-1 rounded-2xl transition-all active:scale-90 hover:scale-105"
                    style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);"
                >
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="white"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    <span class="text-[9px] font-bold" style="color: #cbd5e1;">X / Twitter</span>
                </button>
            </div>

            {{-- Action Buttons --}}
            <div class="flex gap-3 px-5 pb-6">
                <button
                    @click="nativeShare()"
                    id="share-native-btn"
                    class="flex-1 flex items-center justify-center gap-2 font-black text-sm py-3.5 rounded-2xl text-white transition-all active:scale-95 hover:opacity-90"
                    style="background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%); box-shadow: 0 8px 24px rgba(37,99,235,0.35);"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                    {{ __('rankings.share_ranking') }}
                </button>
                <button
                    @click="downloadCard()"
                    id="share-download-btn"
                    class="flex items-center justify-center gap-2 font-bold text-sm px-4 py-3.5 rounded-2xl transition-all active:scale-95"
                    style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); color: #94a3b8;"
                    aria-label="{{ __('profile.share_card_download') }}"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M16 12l-4 4m0 0l-4-4m4 4V4"/></svg>
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Header -->
    <div class="space-y-3 animate-fade-in-up">
        <!-- Title row: name + share button inline -->
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <h1 class="text-lg sm:text-2xl font-bold text-theme tracking-tight truncate">{{ __('rankings.title') }}</h1>
                <p class="text-[11px] text-theme-secondary mt-0.5 hidden sm:block">{{ __('rankings.subtitle') }}</p>
            </div>
            @auth
                @if($currentUserPosition)
                    <button @click="toggleCard()" class="shrink-0 inline-flex items-center gap-1.5 text-xs font-semibold bg-blue-600/10 hover:bg-blue-600/20 text-blue-400 border border-blue-500/20 hover:border-blue-500/30 px-3 py-2 rounded-xl transition-all active:scale-95">
                        <span aria-hidden="true">📤</span>
                        <span class="hidden sm:inline">{{ __('rankings.share_ranking') }}</span>
                        <span class="sm:hidden">#{{ $currentUserPosition }}</span>
                    </button>
                @else
                    <p class="shrink-0 text-[10px] text-theme-muted italic hidden sm:block">{{ __('rankings.share_rank_not_found') }}</p>
                @endif
            @endauth
        </div>

        <!-- Filters row: search + district side by side -->
        <div class="flex gap-2">
            <div class="relative flex-1 min-w-0">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('rankings.search_users') }}" class="glass-input pl-9 pr-3 py-2 rounded-xl text-xs w-full">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-theme-muted pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
            </div>
            <label for="district-filter" class="sr-only">{{ __('rankings.filter_district') }}</label>
            <select id="district-filter" wire:model.live="district" class="glass-input px-2.5 py-2 rounded-xl text-xs shrink-0 max-w-[45%]">
                <option value="">{{ __('rankings.all_districts') }}</option>
                @foreach($districts as $d)
                    <option value="{{ $d }}">{{ $d }}</option>
                @endforeach
            </select>
        </div>

        <!-- Period tabs -->
        <div class="bg-theme-card p-1 rounded-xl border border-theme-subtle flex gap-1 text-xs overflow-x-auto scrollbar-none" role="tablist" aria-label="{{ __('rankings.period') }}">
            <button wire:click="$set('type', 'general')" role="tab" aria-selected="{{ $type === 'general' ? 'true' : 'false' }}" class="flex-1 px-2.5 py-1.5 rounded-lg transition-all whitespace-nowrap active:scale-95 {{ $type === 'general' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'text-theme-secondary hover:text-theme' }}">
                {{ __('rankings.general') }}
            </button>
            <button wire:click="$set('type', 'monthly')" role="tab" aria-selected="{{ $type === 'monthly' ? 'true' : 'false' }}" class="flex-1 px-2.5 py-1.5 rounded-lg transition-all whitespace-nowrap active:scale-95 {{ $type === 'monthly' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'text-theme-secondary hover:text-theme' }}">
                {{ __('rankings.monthly') }}
            </button>
            <button wire:click="$set('type', 'weekly')" role="tab" aria-selected="{{ $type === 'weekly' ? 'true' : 'false' }}" class="flex-1 px-2.5 py-1.5 rounded-lg transition-all whitespace-nowrap active:scale-95 {{ $type === 'weekly' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'text-theme-secondary hover:text-theme' }}">
                {{ __('rankings.weekly') }}
            </button>
            <button wire:click="$set('type', 'daily')" role="tab" aria-selected="{{ $type === 'daily' ? 'true' : 'false' }}" class="flex-1 px-2.5 py-1.5 rounded-lg transition-all whitespace-nowrap active:scale-95 {{ $type === 'daily' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'text-theme-secondary hover:text-theme' }}">
                {{ __('rankings.daily') }}
            </button>
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
            <button wire:click="openUser({{ $userItem->id }}, {{ $position }}, {{ $userItem->rank_score }})" type="button" class="w-full text-left glass-card rounded-2xl border border-theme-medium px-4 py-3 flex items-center gap-3 card-lift hover:bg-white/[0.04] active:scale-[0.98] transition-all cursor-pointer {{ $isTop3 ? 'bg-blue-500/5 border-blue-500/20' : '' }}">
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
                <div class="shrink-0 text-theme-muted text-xs" aria-hidden="true">›</div>
            </button>
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
                    <tr wire:click="openUser({{ $userItem->id }}, {{ $position }}, {{ $userItem->rank_score }})" class="transition-colors hover:bg-white/[0.04] cursor-pointer {{ $isTop3 ? 'bg-blue-500/5' : '' }}">
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

    <!-- User Detail Modal -->
    @if($showUserModal && $selectedUser)
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-black/70 backdrop-blur-sm p-4" wire:click="closeUserModal" x-on:keydown.escape.window="$wire.closeUserModal()">
            <div class="bg-theme-card rounded-2xl border border-theme-medium shadow-2xl max-w-lg w-full max-h-[85vh] overflow-y-auto" @click.stop x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <!-- Header -->
                <div class="p-5 border-b border-theme-subtle">
                    <div class="flex items-center justify-between mb-4">
                        <button wire:click="closeUserModal" class="text-theme-muted hover:text-theme text-xs transition-colors flex items-center gap-1">
                            ‹ {{ __('common.filter_all') }}
                        </button>
                        <button wire:click="closeUserModal" class="text-theme-muted hover:text-theme text-sm transition-colors">✕</button>
                    </div>
                    <div class="flex items-center gap-3">
                        @if($selectedUser->avatar)
                            <img src="{{ $selectedUser->avatar }}" alt="{{ $selectedUser->username }}" class="w-12 h-12 rounded-full border-2 border-blue-500/30 object-cover">
                        @else
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500/20 to-teal-500/20 border-2 border-blue-500/30 flex items-center justify-center text-lg font-bold text-blue-400">
                                {{ strtoupper(substr($selectedUser->username, 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <h2 class="text-lg font-bold text-theme">{{ $selectedUser->username }}</h2>
                            <div class="flex items-center gap-2 text-xs text-theme-secondary">
                                @if($userPosition)
                                    <span class="inline-flex items-center gap-1 bg-blue-600/10 text-blue-400 border border-blue-500/20 px-2 py-0.5 rounded-full font-bold">
                                        #{{ $userPosition }}
                                    </span>
                                @endif
                                <span>{{ __('rankings.pts') }}: <strong class="text-blue-400">{{ $userRankScore }}</strong></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-3 gap-3 p-5">
                    <div class="text-center bg-theme-card rounded-xl border border-theme-subtle p-3">
                        <div class="text-xl font-extrabold text-blue-400">{{ $selectedUser->score }}</div>
                        <div class="text-[10px] text-theme-muted uppercase tracking-wide mt-0.5">{{ __('rankings.score') }}</div>
                    </div>
                    <div class="text-center bg-theme-card rounded-xl border border-theme-subtle p-3">
                        <div class="text-xl font-extrabold text-green-400">{{ $selectedUser->accepted_confirmations_count }}</div>
                        <div class="text-[10px] text-theme-muted uppercase tracking-wide mt-0.5">{{ __('rankings.confirmations') }}</div>
                    </div>
                    <div class="text-center bg-theme-card rounded-xl border border-theme-subtle p-3">
                        <div class="text-xl font-extrabold text-amber-400">{{ $selectedUser->confirmations_count }}</div>
                        <div class="text-[10px] text-theme-muted uppercase tracking-wide mt-0.5">{{ __('rankings.total_reports') }}</div>
                    </div>
                </div>

                <!-- Accuracy -->
                @php
                    $accuracy = $selectedUser->confirmations_count > 0
                        ? round(($selectedUser->accepted_confirmations_count / $selectedUser->confirmations_count) * 100)
                        : 0;
                @endphp
                <div class="px-5 pb-3">
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span class="text-theme-secondary">{{ __('rankings.accuracy') }}</span>
                        <span class="font-bold text-theme">{{ $accuracy }}%</span>
                    </div>
                    <div class="w-full bg-theme-subtle rounded-full h-2 overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500 {{ $accuracy >= 80 ? 'bg-green-500' : ($accuracy >= 50 ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ $accuracy }}%"></div>
                    </div>
                </div>

                <!-- Beaches confirmed -->
                @if(count($userBeaches) > 0)
                    <div class="p-5 border-t border-theme-subtle">
                        <h3 class="text-xs font-bold text-theme-secondary uppercase tracking-wider mb-3">{{ __('rankings.confirmed_beaches') }} ({{ count($userBeaches) }})</h3>
                        <div class="space-y-2">
                            @foreach($userBeaches as $beach)
                                <div class="flex items-center justify-between bg-theme-card rounded-xl border border-theme-subtle px-3 py-2.5 hover:bg-white/[0.03] transition-colors">
                                    <div class="min-w-0 flex-1">
                                        <div class="font-semibold text-theme text-xs truncate">{{ $beach['name'] }}</div>
                                        <div class="text-[10px] text-theme-muted mt-0.5">{{ $beach['district'] }}{{ $beach['municipality'] ? ', ' . $beach['municipality'] : '' }}</div>
                                    </div>
                                    <div class="text-right shrink-0 ml-2">
                                        <div class="text-xs font-bold text-blue-400">{{ $beach['confirmations'] }}×</div>
                                        <div class="text-[9px] text-theme-muted">Reporte</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="p-5 border-t border-theme-subtle text-center text-theme-muted text-xs py-6">
                        {{ __('rankings.no_beaches_confirmed') }}
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
