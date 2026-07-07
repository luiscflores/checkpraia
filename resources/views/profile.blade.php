<x-layouts.app>
    @section('title', 'Perfil CheckPraia - Gestão de Conta')

    <div class="space-y-6 max-w-4xl mx-auto">
        <!-- Profile Header card -->
        <div class="glass-card p-6 md:p-8 rounded-3xl border border-theme-subtle/50 relative overflow-hidden shadow-xl shadow-black/[0.02]">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600/10 via-indigo-600/5 to-transparent pointer-events-none"></div>
            <div class="absolute -right-24 -top-24 w-96 h-96 rounded-full bg-blue-500/10 blur-3xl pointer-events-none"></div>
            
            <div class="relative z-10">
                <span class="text-[11px] uppercase tracking-widest text-blue-400 bg-blue-500/10 px-3 py-1 rounded-full border border-blue-500/20 font-extrabold shadow-sm">
                    Definições de Conta
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-theme tracking-tight mt-2.5">Área Pessoal</h1>
                <p class="text-slate-400 text-sm mt-1.5 font-medium">Gere a tua informação de perfil, palavra-passe e preferências de utilizador.</p>
            </div>
        </div>

        <!-- Update profile information -->
        <div class="glass-card p-6 sm:p-8 rounded-3xl border border-theme-subtle/50 relative overflow-hidden shadow-lg shadow-black/[0.02]">
            <div class="max-w-xl">
                <livewire:profile.update-profile-information-form />
            </div>
        </div>

        <!-- Update password -->
        <div class="glass-card p-6 sm:p-8 rounded-3xl border border-theme-subtle/50 relative overflow-hidden shadow-lg shadow-black/[0.02]">
            <div class="max-w-xl">
                <livewire:profile.update-password-form />
            </div>
        </div>

        <!-- Delete user -->
        <div class="glass-card p-6 sm:p-8 rounded-3xl border border-theme-subtle/50 relative overflow-hidden shadow-lg shadow-black/[0.02]">
            <div class="max-w-xl">
                <livewire:profile.delete-user-form />
            </div>
        </div>
    </div>
</x-layouts.app>
