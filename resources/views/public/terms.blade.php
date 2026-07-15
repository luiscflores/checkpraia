@props(['title' => __('common.terms_page_title')])

<x-layouts.app>
    <x-slot:title>{{ $title }}</x-slot:title>

    @section('meta_description', __('common.terms_page_subtitle'))
    @section('og_title', $title)
    @section('og_description', __('common.terms_page_subtitle'))
    @section('og_type', 'website')

    @section('ld_json')
    @parent
    <script type="application/ld+json">
    @php echo json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        '@id' => url('/termos') . '#webpage',
        'name' => $title,
        'description' => __('common.terms_page_subtitle'),
        'url' => url('/termos'),
        'isPartOf' => ['@id' => url('/') . '#website'],
        'about' => ['@type' => 'Thing', 'name' => 'Terms of Service'],
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); @endphp
    </script>
    @endsection

    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 space-y-8">
        <div class="text-center space-y-3">
            <div class="text-5xl mb-2">📜</div>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">{{ $title }}</h1>
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
