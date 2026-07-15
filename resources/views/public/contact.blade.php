@props(['title' => __('common.contact_page_title')])

<x-layouts.app>
    <x-slot:title>{{ $title }}</x-slot:title>

    @section('meta_description', __('common.contact_page_subtitle'))
    @section('og_title', $title)
    @section('og_description', __('common.contact_page_subtitle'))
    @section('og_type', 'website')

    @section('ld_json')
    @parent
    <script type="application/ld+json">
    @php echo json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'ContactPage',
        '@id' => url('/contactos') . '#contactpage',
        'name' => $title,
        'description' => __('common.contact_page_subtitle'),
        'url' => url('/contactos'),
        'isPartOf' => ['@id' => url('/') . '#website'],
        'mainEntity' => [
            '@type' => 'Person',
            'name' => __('common.contact_email_address'),
            'email' => str_replace('mailto:', '', __('common.contact_email_address')),
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); @endphp
    </script>
    @endsection

    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 space-y-10">
        {{-- Hero --}}
        <div class="text-center space-y-3">
            <div class="text-5xl mb-2">📬</div>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">{{ $title }}</h1>
            <p class="text-theme-secondary max-w-xl mx-auto leading-relaxed">{{ __('common.contact_page_subtitle') }}</p>
        </div>

        {{-- Contact Form --}}
        <div class="glass-card p-6 sm:p-8 rounded-2xl">
            <form wire:submit.prevent="send" class="space-y-5">
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="name" class="block text-xs font-bold uppercase tracking-wider text-theme-secondary mb-1.5">{{ __('common.contact_form_name') }}</label>
                        <input type="text" id="name" name="name" required
                               class="w-full px-4 py-2.5 rounded-xl bg-theme-input border border-theme-medium text-sm text-theme focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all placeholder-theme-muted">
                    </div>
                    <div>
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-theme-secondary mb-1.5">{{ __('common.contact_form_email') }}</label>
                        <input type="email" id="email" name="email" required
                               class="w-full px-4 py-2.5 rounded-xl bg-theme-input border border-theme-medium text-sm text-theme focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all placeholder-theme-muted">
                    </div>
                </div>
                <div>
                    <label for="subject" class="block text-xs font-bold uppercase tracking-wider text-theme-secondary mb-1.5">{{ __('common.contact_form_subject') }}</label>
                    <input type="text" id="subject" name="subject" required
                           class="w-full px-4 py-2.5 rounded-xl bg-theme-input border border-theme-medium text-sm text-theme focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all placeholder-theme-muted">
                </div>
                <div>
                    <label for="message" class="block text-xs font-bold uppercase tracking-wider text-theme-secondary mb-1.5">{{ __('common.contact_form_message') }}</label>
                    <textarea id="message" name="message" rows="5" required
                              class="w-full px-4 py-2.5 rounded-xl bg-theme-input border border-theme-medium text-sm text-theme focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all placeholder-theme-muted resize-y"></textarea>
                </div>
                <button type="submit"
                        class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold transition-all hover:scale-[1.02] active:scale-[0.98]">
                    {{ __('common.contact_form_submit') }}
                </button>
            </form>
        </div>

        {{-- Email --}}
        <div class="glass-card p-6 sm:p-8 rounded-2xl text-center space-y-2">
            <span class="text-3xl block">✉️</span>
            <h2 class="text-lg font-bold">{{ __('common.contact_email_us') }}</h2>
            <p class="text-theme-secondary text-sm">{{ __('common.contact_email_us_description') }}</p>
            <a href="mailto:{{ __('common.contact_email_address') }}" class="inline-block mt-2 text-blue-400 hover:text-blue-300 font-bold text-sm transition-colors">
                {{ __('common.contact_email_address') }}
            </a>
        </div>

        <x-ads.slot slot="contact_bottom" />

        {{-- Social --}}
        <div class="glass-card p-6 sm:p-8 rounded-2xl text-center space-y-2">
            <span class="text-3xl block">🌐</span>
            <h2 class="text-lg font-bold">{{ __('common.contact_social') }}</h2>
            <p class="text-theme-secondary text-sm">{{ __('common.contact_social_description') }}</p>
            <div class="flex justify-center gap-4 mt-3">
                <a href="#" class="text-theme-secondary hover:text-blue-400 transition-colors text-2xl" aria-label="Facebook">📘</a>
                <a href="#" class="text-theme-secondary hover:text-blue-400 transition-colors text-2xl" aria-label="Instagram">📸</a>
                <a href="#" class="text-theme-secondary hover:text-blue-400 transition-colors text-2xl" aria-label="Twitter/X">🐦</a>
            </div>
        </div>
    </div>
</x-layouts.app>
