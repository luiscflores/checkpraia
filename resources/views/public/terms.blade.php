@props(['title' => __('common.terms_page_title')])

<x-layouts.app>
    <x-slot:title>{{ $title }}</x-slot:title>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 space-y-8">
        <div class="text-center space-y-3">
            <div class="text-5xl mb-2">📜</div>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">{{ __('common.terms_page_title') }}</h1>
            <p class="text-theme-secondary text-sm">{{ __('common.terms_last_update') }}</p>
        </div>

        <div class="glass-card p-6 sm:p-8 rounded-2xl space-y-6 text-sm text-theme-secondary leading-relaxed">
            <section class="space-y-2">
                <h2 class="text-lg font-bold text-theme">1. {{ __('common.terms_acceptance_title') }}</h2>
                <p>{{ __('common.terms_acceptance') }}</p>
            </section>

            <section class="space-y-2">
                <h2 class="text-lg font-bold text-theme">2. {{ __('common.terms_service_title') }}</h2>
                <p>{{ __('common.terms_service') }}</p>
            </section>

            <section class="space-y-2">
                <h2 class="text-lg font-bold text-theme">3. {{ __('common.terms_user_title') }}</h2>
                <p>{{ __('common.terms_user') }}</p>
                <ul class="list-disc pl-5 space-y-1">
                    <li>{{ __('common.terms_user_item1') }}</li>
                    <li>{{ __('common.terms_user_item2') }}</li>
                    <li>{{ __('common.terms_user_item3') }}</li>
                    <li>{{ __('common.terms_user_item4') }}</li>
                </ul>
            </section>

            <section class="space-y-2">
                <h2 class="text-lg font-bold text-theme">4. {{ __('common.terms_disclaimer_title') }}</h2>
                <p>{{ __('common.terms_disclaimer') }}</p>
            </section>

            <section class="space-y-2">
                <h2 class="text-lg font-bold text-theme">5. {{ __('common.terms_changes_title') }}</h2>
                <p>{{ __('common.terms_changes') }}</p>
            </section>
        </div>
    </div>
</x-layouts.app>
