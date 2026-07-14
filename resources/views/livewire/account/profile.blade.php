<div class="space-y-8">
    @section('title', __('profile.page_title'))

    @if (session()->has('auth_success'))
        <div class="p-3 bg-emerald-500/20 border border-emerald-500/30 text-emerald-200 text-xs rounded-xl font-medium animate-fade-in">
            ✔️ {{ session('auth_success') }}
        </div>
    @endif

    <!-- Stats Header Dashboard Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4" data-animate-stagger="0.08">
        <!-- Score Card -->
        <div class="glass-card p-4 sm:p-6 rounded-2xl border border-theme-medium flex items-center justify-between">
            <div>
                <span class="text-xs text-theme-secondary uppercase font-bold tracking-wider block">{{ __('profile.total_score') }}</span>
                <span class="text-3xl font-black text-theme block mt-1">{{ auth()->user()->score }} <span class="text-xs text-theme-muted font-normal">{{ __('profile.pts') }}</span></span>
            </div>
            <span class="text-3xl">🏆</span>
        </div>

        <!-- Approved Card -->
        <div class="glass-card p-4 sm:p-6 rounded-2xl border border-theme-medium flex items-center justify-between">
            <div>
                <span class="text-xs text-theme-secondary uppercase font-bold tracking-wider block">{{ __('profile.confirmed_accepted') }}</span>
                <span class="text-3xl font-black text-emerald-400 block mt-1">{{ auth()->user()->accepted_confirmations_count }}</span>
            </div>
            <span class="text-3xl text-emerald-400">✔️</span>
        </div>

        <!-- Penalized Card -->
        <div class="glass-card p-4 sm:p-6 rounded-2xl border border-theme-medium flex items-center justify-between">
            <div>
                <span class="text-xs text-theme-secondary uppercase font-bold tracking-wider block">{{ __('profile.confirmed_penalized') }}</span>
                <span class="text-3xl font-black text-rose-400 block mt-1">{{ auth()->user()->penalized_confirmations_count }}</span>
            </div>
            <span class="text-3xl text-rose-400">❌</span>
        </div>

        <!-- Referrals Card -->
        <div class="glass-card p-4 sm:p-6 rounded-2xl border border-theme-medium flex items-center justify-between">
            <div>
                <span class="text-xs text-theme-secondary uppercase font-bold tracking-wider block">{{ __('profile.friends_invited') }}</span>
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
            <div class="glass-card p-6 rounded-3xl space-y-4 animate-fade-in-up" data-animate>
                <h3 class="text-lg font-bold text-theme">{{ __('profile.settings') }}</h3>
                
                @if(session()->has('profile_success'))
                    <div class="p-3 bg-emerald-500/20 border border-emerald-500/30 text-emerald-200 text-xs rounded-xl font-medium">
                        ✔️ {{ session('profile_success') }}
                    </div>
                @endif

                <form wire:submit.prevent="updateProfile" class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-xs text-theme-secondary font-bold block">{{ __('profile.full_name') }}</label>
                        <input type="text" wire:model="editName" class="w-full glass-input px-3.5 py-2.5 rounded-xl text-base sm:text-sm" />
                        @error('editName') <span class="text-xs text-rose-400 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs text-theme-secondary font-bold block">{{ __('profile.username') }}</label>
                        <input type="text" wire:model="editUsername" class="w-full glass-input px-3.5 py-2.5 rounded-xl text-base sm:text-sm" />
                        @error('editUsername') <span class="text-xs text-rose-400 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs text-theme-secondary font-bold block">{{ __('profile.language') }}</label>
                        <select wire:model="editLocale" class="w-full glass-input px-3.5 py-2.5 rounded-xl text-base sm:text-sm">
                            @foreach(config('locales.supported', ['pt', 'en', 'es', 'fr']) as $code)
                                <option value="{{ $code }}">{{ config('locales.flags.' . $code) }} {{ __("common.lang_{$code}") }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Push Notifications Toggle -->
                    <div x-data="pushHandler()" x-init="$watch('ready', v => { if(v && !subscribed && new URLSearchParams(window.location.search).get('auto_subscribe') === '1') { toggle(); history.replaceState(null, '', window.location.pathname); } })" class="space-y-2">
                        <div class="flex items-center justify-between p-3.5 bg-theme-card rounded-xl border border-theme-subtle">
                            <div class="flex items-center gap-3">
                                <span class="text-lg">🔔</span>
                                <div>
                                    <span class="text-xs font-bold text-theme block">{{ __('profile.push_notifications') }}</span>
                                    <span class="text-[11px] text-theme-muted" x-show="!loading">
                                        <template x-if="subscribed">
                                            <span class="text-emerald-400">{{ __('common.push_enabled') }}</span>
                                        </template>
                                        <template x-if="!subscribed">
                                            <span x-text="'{{ __('common.push_enable') }}'"></span>
                                        </template>
                                    </span>
                                    <span class="text-[11px] text-theme-muted" x-show="loading">{{ __('profile.updating') }}...</span>
                                </div>
                            </div>
                            <button @click="toggle()" :disabled="loading" class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:ring-offset-2 focus:ring-offset-theme-card disabled:opacity-50" :class="subscribed ? 'bg-blue-600' : 'bg-theme-subtle'" role="switch" :aria-checked="subscribed" :aria-label="subscribed ? '{{ __('common.push_enabled') }}' : '{{ __('common.push_enable') }}'">
                                <span class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out translate" :class="subscribed ? 'translate-x-5' : 'translate-x-0'"></span>
                            </button>
                        </div>
                        <p x-show="ready && !subscribed" class="text-[11px] text-theme-muted px-1">{{ __('profile.push_notifications_description') }}</p>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-500 active:scale-[0.98] text-white font-bold py-3 sm:py-2.5 rounded-xl text-sm transition-all touch-target">
                            {{ __('profile.save_changes') }}
                        </button>
                        <button type="button" wire:click="logout" @click="localStorage.removeItem('checkpraia-active-region')" class="bg-theme-card active:scale-[0.98] text-theme-secondary font-semibold px-5 sm:px-4 py-3 sm:py-2.5 rounded-xl text-sm border border-theme-medium transition-all touch-target hover:text-theme">
                            {{ __('profile.logout') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Referrals Card -->
            <div class="glass-card p-6 rounded-3xl space-y-4 relative overflow-hidden animate-fade-in-up" data-animate>
                <h3 class="text-lg font-bold text-theme flex items-center gap-2">
                    👥 {{ __('profile.invite_friend') }}
                </h3>
                <p class="text-xs text-theme-secondary leading-relaxed">
                    {{ __('profile.invite_description') }}
                </p>

                @php $referralLink = route('register', ['ref' => auth()->user()->referral_code]); @endphp
                <div class="bg-theme-card p-4 rounded-2xl border border-theme-subtle space-y-2">
                    <span class="text-xs text-theme-secondary font-bold block uppercase tracking-wider">{{ __('profile.your_link') }}</span>
                    <div class="flex items-center justify-between bg-theme-card px-3.5 py-2 rounded-xl border border-theme-medium gap-2">
                        <span class="text-xs text-blue-400 truncate select-all">{{ $referralLink }}</span>
                        <button onclick="navigator.clipboard.writeText('{{ $referralLink }}'); alert('{{ __('profile.code_copied') }}');" class="shrink-0 text-xs text-blue-400 hover:underline font-semibold">{{ __('profile.copy') }}</button>
                    </div>
                </div>

                <!-- Points earned info -->
                <div class="bg-blue-500/10 border border-blue-500/20 p-4 rounded-2xl space-y-1">
                    <span class="text-xs text-blue-400 font-bold block uppercase tracking-wider">{{ __('profile.points_per_referral') }}</span>
                    <span class="text-lg font-black text-blue-400 block">+{{ config('gamification.referrals.bonus_points') }} {{ __('profile.pts') }}</span>
                    <span class="text-xs text-theme-secondary leading-relaxed block">{{ __('profile.points_per_referral_detail') }}</span>
                </div>

                <!-- Referral progress tracker -->
                <div class="space-y-1.5 pt-2">
                    <div class="flex justify-between text-xs font-semibold">
                        <span class="text-theme-secondary">{{ __('profile.bonus_progress') }}</span>
                        <span class="text-theme">{{ __('profile.invites_count', ['progress' => $referralProgress]) }}</span>
                    </div>
                    <div class="w-full bg-theme-card h-2.5 rounded-full overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-500 to-teal-400 h-full transition-all duration-500" style="width: {{ ($referralProgress / 5) * 100 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Favorites and Logs (Span 7) -->
        <div class="lg:col-span-7 space-y-6">
            
            <!-- Favorites list -->
            <div class="glass-card p-6 rounded-3xl space-y-4 animate-fade-in-up" data-animate>
                <h3 class="text-lg font-bold text-theme flex items-center gap-2">
                    ⭐ {{ __('profile.favorites') }}
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
                                    $favFlag = $favorite->getDisplayFlag();
                                    $favColor = match($favFlag) {
                                        'green' => 'bg-emerald-500 text-slate-950',
                                        'yellow' => 'bg-amber-500 text-slate-950',
                                        'red' => 'bg-rose-500 text-white',
                                        'blue_or_neutral' => 'bg-blue-600 text-white',
                                        default => 'bg-slate-600 text-slate-300'
                                    };
                                    $favFlagName = match($favFlag) {
                                        'green' => __('common.flag_green'),
                                        'yellow' => __('common.flag_yellow'),
                                        'red' => __('common.flag_red'),
                                        'blue_or_neutral' => __('common.flag_blue_or_neutral'),
                                        default => '🔘'
                                    };
                                @endphp
                                <span class="px-2.5 py-1 text-xs font-extrabold rounded-full {{ $favColor }}">
                                    {{ $favFlagName }}
                                </span>
                                
                                <button wire:click="removeFavorite({{ $favorite->id }})" class="text-theme-muted hover:text-rose-400 text-sm transition-colors" title="{{ __('common.favorite_remove') }}">
                                    🗑️
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-theme-muted text-center py-4">{{ __('profile.no_favorites') }}</p>
                    @endforelse
                </div>
            </div>

            <!-- Confirmations history -->
            <div class="glass-card p-6 rounded-3xl space-y-4 animate-fade-in-up" data-animate>
                <h3 class="text-lg font-bold text-theme flex items-center gap-2">
                    📋 {{ __('profile.reports_history') }}
                </h3>

                <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                    @forelse($reports as $report)
                        <div class="bg-theme-card border border-theme-subtle p-3 rounded-2xl flex items-center justify-between text-xs">
                            <div class="space-y-1">
                                <span class="font-bold text-theme block">{{ $report->beach->name }}</span>
                                <span class="text-xs text-theme-muted block">{{ __('profile.report_date', ['date' => $report->reported_at->format('d/m/Y H:i')]) }} &bull; GPS: {{ round($report->distance_to_beach, 2) }} km</span>
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
                                        'confirmed' => __('profile.status_accepted'),
                                        'rejected' => __('profile.status_penalized'),
                                        'cancelled' => __('profile.status_cancelled'),
                                        default => __('profile.status_pending')
                                    };
                                @endphp
                                <span class="font-bold {{ $histColor }} uppercase tracking-wider text-xs">
                                    {{ __('profile.flag_label', ['flag' => match($report->flag) { 'green' => __('common.flag_green'), 'yellow' => __('common.flag_yellow'), default => __('common.flag_red') }]) }}
                                </span>

                                <span class="px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wider {{ $statusBadge }}">
                                    {{ $statusName }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-theme-muted text-center py-4">{{ __('profile.no_reports') }}</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Delete Account (mobile: last, desktop: left column) -->
        <div class="order-last lg:order-none lg:col-span-5 glass-card p-6 rounded-3xl space-y-3 border-rose-500/20 animate-fade-in-up" data-animate>
            <h3 class="text-sm font-bold text-rose-400 uppercase tracking-wider">{{ __('profile.danger_zone') }}</h3>
            <p class="text-xs text-theme-muted leading-relaxed">{{ __('profile.danger_description') }}</p>
            <button onclick="if(confirm('{{ __('profile.delete_confirm') }}')) { @this.call('deleteAccount') }" class="w-full bg-rose-500/10 hover:bg-rose-500 active:scale-[0.98] text-rose-400 hover:text-white border border-rose-500/20 hover:border-rose-500 font-bold py-3 sm:py-2.5 rounded-xl text-sm transition-all touch-target">
                {{ __('profile.delete_button') }}
            </button>
        </div>

    </div>
</div>
