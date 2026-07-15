@props(['title' => __('common.privacy_page_title')])

<x-layouts.app>
    <x-slot:title>{{ $title }}</x-slot:title>

    @section('meta_description', __('common.privacy_page_subtitle'))
    @section('og_title', $title)
    @section('og_description', __('common.privacy_page_subtitle'))
    @section('og_type', 'website')

    @section('ld_json')
    @parent
    <script type="application/ld+json">
    @php echo json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        '@id' => url('/privacidade') . '#webpage',
        'name' => $title,
        'description' => __('common.privacy_page_subtitle'),
        'url' => url('/privacidade'),
        'isPartOf' => ['@id' => url('/') . '#website'],
        'about' => ['@type' => 'Thing', 'name' => 'Privacy Policy'],
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); @endphp
    </script>
    @endsection

    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 space-y-8">
        <div class="text-center space-y-3">
            <div class="text-5xl mb-2">🔒</div>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">{{ $title }}</h1>
            <p class="text-theme-secondary text-sm">{{ __('common.privacy_last_update') }}</p>
        </div>

        <div class="glass-card p-6 sm:p-8 rounded-2xl space-y-6 text-sm text-theme-secondary leading-relaxed">
            <section class="space-y-2">
                <h2 class="text-lg font-bold text-theme">1. {{ __('common.privacy_data_title') }}</h2>
                <p>{{ __('common.privacy_data') }}</p>
                <ul class="list-disc pl-5 space-y-1">
                    <li>{{ __('common.privacy_data_item1') }}</li>
                    <li>{{ __('common.privacy_data_item2') }}</li>
                    <li>{{ __('common.privacy_data_item3') }}</li>
                    <li>{{ __('common.privacy_data_item4') }}</li>
                </ul>
            </section>

            <section class="space-y-2">
                <h2 class="text-lg font-bold text-theme">2. {{ __('common.privacy_location_title') }}</h2>
                <p>{{ __('common.privacy_location') }}</p>
            </section>

            <section class="space-y-2">
                <h2 class="text-lg font-bold text-theme">3. {{ __('common.privacy_cookies_title') }}</h2>
                <p>{{ __('common.privacy_cookies') }}</p>
            </section>

            <section class="space-y-2">
                <h2 class="text-lg font-bold text-theme">4. {{ __('common.privacy_third_party_title') }}</h2>
                <p>{{ __('common.privacy_third_party') }}</p>
            </section>

            <section class="space-y-2">
                <h2 class="text-lg font-bold text-theme">5. {{ __('common.privacy_rights_title') }}</h2>
                <p>{{ __('common.privacy_rights') }}</p>
            </section>

            <section class="space-y-2">
                <h2 class="text-lg font-bold text-theme">6. {{ __('common.privacy_contact_title') }}</h2>
                <p>{{ __('common.privacy_contact') }}</p>
            </section>
        </div>
    </div>
</x-layouts.app>
