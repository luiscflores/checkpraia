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
            $this->addError('referralCodeInput', 'O código de convite inserido não é válido.');
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

        session()->flash('auth_success', 'Conta criada com sucesso!');

        $this->redirect(route('profile', absolute: false));
    }
}; ?>

<div class="max-w-md mx-auto my-12">
    @section('title', 'Registar - CheckPraia')

    <div class="glass-card p-5 sm:p-8 rounded-3xl border border-theme-medium space-y-6 relative overflow-hidden">
        <div class="absolute w-32 h-32 rounded-full blur-3xl opacity-20 -top-12 -right-12 bg-blue-500"></div>

        <h2 class="text-xl font-bold text-theme text-center">Criar Nova Conta</h2>

        <form wire:submit="register" class="space-y-4">
            @if (session()->has('auth_success'))
                <div class="p-3 bg-emerald-500/20 border border-emerald-500/30 text-emerald-200 text-xs rounded-xl font-medium">
                    ✔️ {{ session('auth_success') }}
                </div>
            @endif

            <div class="space-y-1">
                <label class="text-xs text-theme-secondary font-bold uppercase tracking-wider block">Nome Completo</label>
                <input type="text" wire:model="name" class="w-full glass-input px-4 py-2.5 rounded-xl text-base sm:text-sm" placeholder="Ex: Luís Flores" />
                @error('name') <span class="text-xs text-rose-400 block mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="space-y-1">
                <label class="text-xs text-theme-secondary font-bold uppercase tracking-wider block">Nome de Utilizador (Público)</label>
                <input type="text" wire:model="username" class="w-full glass-input px-4 py-2.5 rounded-xl text-base sm:text-sm" placeholder="Ex: luisflores" />
                @error('username') <span class="text-xs text-rose-400 block mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="space-y-1">
                <label class="text-xs text-theme-secondary font-bold uppercase tracking-wider block">Endereço de Email</label>
                <input type="email" wire:model="email" class="w-full glass-input px-4 py-2.5 rounded-xl text-base sm:text-sm" placeholder="exemplo@email.com" />
                @error('email') <span class="text-xs text-rose-400 block mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="space-y-1">
                <label class="text-xs text-theme-secondary font-bold uppercase tracking-wider block">Palavra-passe</label>
                <input type="password" wire:model="password" class="w-full glass-input px-4 py-2.5 rounded-xl text-base sm:text-sm" placeholder="Mínimo 6 caracteres" />
                @error('password') <span class="text-xs text-rose-400 block mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="space-y-1">
                <label class="text-xs text-theme-secondary font-bold uppercase tracking-wider block">Confirmar Palavra-passe</label>
                <input type="password" wire:model="password_confirmation" class="w-full glass-input px-4 py-2.5 rounded-xl text-base sm:text-sm" placeholder="Repete a palavra-passe" />
            </div>

            <div class="space-y-1">
                <label class="text-xs text-theme-secondary font-bold uppercase tracking-wider block">Código de Convite (Opcional)</label>
                <input type="text" wire:model="referralCodeInput" class="w-full glass-input px-4 py-2.5 rounded-xl text-base sm:text-sm" placeholder="Ex: ABC123XYZ" />
                @error('referralCodeInput') <span class="text-xs text-rose-400 block mt-1">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-xl text-sm transition-all shadow-md shadow-blue-500/20">
                Registar Nova Conta &rarr;
            </button>

            <p class="text-xs text-center text-theme-muted pt-2">
                Já tens conta?
                <a href="{{ route('login') }}" class="text-blue-400 hover:underline font-semibold" wire:navigate>Entrar</a>
            </p>
        </form>
    </div>
</div>
