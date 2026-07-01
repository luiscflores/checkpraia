<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();

        $user = \App\Models\User::where('email', $this->form->email)->first();
        if ($user && $user->is_suspended) {
            $this->addError('form.email', 'A tua conta está suspensa. Não é possível iniciar sessão.');
            return;
        }

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('profile', absolute: false));
    }
}; ?>

<div class="max-w-md mx-auto my-12">
    @section('title', 'Entrar - CheckPraia')

    <div class="glass-card p-5 sm:p-8 rounded-3xl border border-theme-medium space-y-6 relative overflow-hidden">
        <div class="absolute w-32 h-32 rounded-full blur-3xl opacity-20 -top-12 -right-12 bg-blue-500"></div>

        <h2 class="text-xl font-bold text-theme text-center">Entrar na tua Conta</h2>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form wire:submit="login" class="space-y-4">
            @error('form.email')
                <div class="p-3 bg-rose-500/20 border border-rose-500/30 text-rose-200 text-xs rounded-xl font-medium">
                    {{ $message }}
                </div>
            @enderror

            <div class="space-y-1">
                <label class="text-xs text-theme-secondary font-bold uppercase tracking-wider block">Endereço de Email</label>
                <input type="email" wire:model="form.email" class="w-full glass-input px-4 py-2.5 rounded-xl text-base sm:text-sm" placeholder="exemplo@email.com" />
                <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
            </div>

            <div class="space-y-1">
                <label class="text-xs text-theme-secondary font-bold uppercase tracking-wider block">Palavra-passe</label>
                <input type="password" wire:model="form.password" class="w-full glass-input px-4 py-2.5 rounded-xl text-base sm:text-sm" placeholder="******" />
                <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between">
                <label class="inline-flex items-center gap-2 text-xs text-theme-secondary">
                    <input type="checkbox" wire:model="form.remember" class="rounded border-theme-medium bg-theme-card text-blue-600">
                    Lembrar-me
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs text-blue-400 hover:underline" wire:navigate>
                        Esqueci-me da palavra-passe
                    </a>
                @endif
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-xl text-sm transition-all shadow-md shadow-blue-500/20">
                Entrar na Conta &rarr;
            </button>

            <div class="relative my-4">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-theme-medium"></div>
                </div>
                <div class="relative flex justify-center text-xs uppercase">
                    <span class="px-2 text-theme-secondary" style="background: var(--bg-glass-bg);">ou</span>
                </div>
            </div>

            <a href="{{ route('auth.google') }}" class="w-full flex items-center justify-center gap-3 bg-white hover:bg-gray-100 text-gray-800 font-bold py-3 rounded-xl text-sm transition-all shadow-md">
                <svg class="w-5 h-5" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Entrar com Google
            </a>

            <p class="text-xs text-center text-theme-muted">
                Ainda não tens conta?
                <a href="{{ route('register') }}" class="text-blue-400 hover:underline font-semibold" wire:navigate>Registar</a>
            </p>
        </form>
    </div>
</div>
