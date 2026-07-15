<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component
{
    public string $name = '';
    public string $username = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $referralCodeInput = '';

    public function mount(): void
    {
        $ref = request()->query('ref');
        if ($ref && User::where('referral_code', strtoupper($ref))->exists()) {
            $this->referralCodeInput = strtoupper($ref);
        }
    }

    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'unique:users,username', 'min:3'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'referralCodeInput' => ['nullable', 'string'],
        ]);

        if ($this->referralCodeInput && !User::where('referral_code', strtoupper($this->referralCodeInput))->exists()) {
            $this->addError('referralCodeInput', __('auth.register.referral_invalid'));
            return;
        }

        $validated['password'] = Hash::make($validated['password']);
        unset($validated['referralCodeInput']);

        $user = User::create([
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'password' => $validated['password'],
            'referral_code' => strtoupper(\Illuminate\Support\Str::random(8)),
            'score' => 0,
        ]);

        if ($this->referralCodeInput) {
            $referrer = User::where('referral_code', strtoupper($this->referralCodeInput))->first();
            if ($referrer) {
                \App\Models\Referral::create([
                    'referrer_user_id' => $referrer->id,
                    'invited_user_id' => $user->id,
                    'code' => strtoupper($this->referralCodeInput),
                    'status' => 'pending',
                ]);
            }
        }

        event(new Registered($user));

        Auth::login($user);

        session()->flash('auth_success', __('auth.register.success'));

        $this->redirect(route('profile', ['auto_subscribe' => 1], absolute: false));
    }
}; ?>

<div class="max-w-md mx-auto my-12">
    @section('title', __('auth.register.page_title'))

    <div class="glass-card p-5 sm:p-8 rounded-3xl border border-theme-medium space-y-6 relative overflow-hidden animate-scale-in">
        <div class="absolute w-32 h-32 rounded-full blur-3xl opacity-20 -top-12 -right-12 bg-blue-500 animate-float"></div>

        <h2 class="text-xl font-bold text-theme text-center">{{ __('auth.register.heading') }}</h2>

        <form wire:submit="register" class="space-y-4">
            @if (session()->has('auth_success'))
                <div class="p-3 bg-emerald-500/20 border border-emerald-500/30 text-emerald-200 text-xs rounded-xl font-medium">
                    ✔️ {{ session('auth_success') }}
                </div>
            @endif

            <div class="space-y-1">
                <label class="text-xs text-theme-secondary font-bold uppercase tracking-wider block">{{ __('auth.register.name_label') }}</label>
                <input type="text" wire:model="name" autocomplete="name" autofocus class="w-full glass-input px-4 py-2.5 rounded-xl text-base sm:text-sm" placeholder="{{ __('auth.register.name_placeholder') }}" />
                @error('name') <span class="text-xs text-rose-400 block mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="space-y-1">
                <label class="text-xs text-theme-secondary font-bold uppercase tracking-wider block">{{ __('auth.register.username_label') }}</label>
                <input type="text" wire:model="username" autocomplete="username" class="w-full glass-input px-4 py-2.5 rounded-xl text-base sm:text-sm" placeholder="{{ __('auth.register.username_placeholder') }}" />
                @error('username') <span class="text-xs text-rose-400 block mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="space-y-1">
                <label class="text-xs text-theme-secondary font-bold uppercase tracking-wider block">{{ __('auth.register.email_label') }}</label>
                <input type="email" wire:model="email" autocomplete="email" class="w-full glass-input px-4 py-2.5 rounded-xl text-base sm:text-sm" placeholder="{{ __('auth.register.email_placeholder') }}" />
                @error('email') <span class="text-xs text-rose-400 block mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="space-y-1" x-data="{
                strength: 0,
                get color() { return this.strength < 2 ? '#ef4444' : this.strength < 3 ? '#f59e0b' : this.strength < 4 ? '#10b981' : '#06b6d4'; },
                get label() { return this.strength < 2 ? 'Fraca' : this.strength < 3 ? 'Razoável' : this.strength < 4 ? 'Forte' : 'Muito forte'; },
                calc(pw) {
                    let s = 0;
                    if (pw.length >= 8) s++;
                    if (/[A-Z]/.test(pw) && /[a-z]/.test(pw)) s++;
                    if (/\d/.test(pw)) s++;
                    if (/[^A-Za-z0-9]/.test(pw)) s++;
                    this.strength = s;
                }
            }">
                <label class="text-xs text-theme-secondary font-bold uppercase tracking-wider block">{{ __('auth.register.password_label') }}</label>
                <input type="password" wire:model="password" autocomplete="new-password" @input="calc($event.target.value)" class="w-full glass-input px-4 py-2.5 rounded-xl text-base sm:text-sm" placeholder="{{ __('auth.register.password_placeholder') }}" />
                <div class="mt-1.5">
                    <div class="flex gap-1">
                        <template x-for="i in 4" :key="i">
                            <div class="h-1 flex-1 rounded-full transition-colors duration-300" :style="`background: ${i <= strength ? color : 'rgba(255,255,255,0.15)'}`"></div>
                        </template>
                    </div>
                    <p x-show="strength > 0" x-transition class="text-xs mt-1 font-medium" :style="`color: ${color}`" x-text="label"></p>
                </div>
                @error('password') <span class="text-xs text-rose-400 block mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="space-y-1">
                <label class="text-xs text-theme-secondary font-bold uppercase tracking-wider block">{{ __('auth.register.password_confirm_label') }}</label>
                <input type="password" wire:model="password_confirmation" autocomplete="new-password" class="w-full glass-input px-4 py-2.5 rounded-xl text-base sm:text-sm" placeholder="{{ __('auth.register.password_confirm_placeholder') }}" />
            </div>

            <div x-data="{ showReferral: @js(request()->has('ref')) }">
                <template x-if="!showReferral">
                    <button type="button" @click="showReferral = true" class="text-xs text-theme-secondary hover:text-theme underline mt-2">
                        {{ __('auth.register.has_referral') ?? 'Tenho código de convite' }}
                    </button>
                </template>
                <template x-if="showReferral">
                    <div class="space-y-1">
                        <label class="text-xs text-theme-secondary font-bold uppercase tracking-wider block">{{ __('auth.register.referral_label') }}</label>
                        <input type="text" wire:model="referralCodeInput" class="w-full glass-input px-4 py-2.5 rounded-xl text-base sm:text-sm" placeholder="{{ __('auth.register.referral_placeholder') }}" />
                        @error('referralCodeInput') <span class="text-xs text-rose-400 block mt-1">{{ $message }}</span> @enderror
                    </div>
                </template>
            </div>

            <button type="submit" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-not-allowed" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-xl text-sm transition-all shadow-md shadow-blue-500/20">
                <span wire:loading.remove>{{ __('auth.register.submit') }} &rarr;</span>
                <span wire:loading class="flex items-center justify-center gap-2">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    {{ __('common.loading') ?? 'A carregar...' }}
                </span>
            </button>

            <p class="text-xs text-center text-theme-muted pt-2">
                {{ __('auth.register.has_account') }}
                <a href="{{ route('login') }}" class="text-blue-400 hover:underline font-semibold" wire:navigate>{{ __('auth.register.login_link') }}</a>
            </p>
        </form>
    </div>
</div>
