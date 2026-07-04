<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">
    @foreach($staticPages as $page)
        <url>
            <loc>{{ $page['loc'] }}</loc>
            <priority>{{ $page['priority'] }}</priority>
            <changefreq>{{ $page['changefreq'] }}</changefreq>
        </url>
    @endforeach
    @foreach($beaches as $beach)
        @foreach($locales as $locale => $prefix)
            <url>
                <loc>{{ url($locale === 'pt' ? "/{$prefix}/{$beach->slug}" : "/{$locale}/{$prefix}/{$beach->slug}") }}</loc>
                <priority>0.7</priority>
                <changefreq>hourly</changefreq>
                @foreach($locales as $altLocale => $altPrefix)
                    <xhtml:link rel="alternate" hreflang="{{ $altLocale }}" href="{{ url($altLocale === 'pt' ? "/{$altPrefix}/{$beach->slug}" : "/{$altLocale}/{$altPrefix}/{$beach->slug}") }}" />
                @endforeach
            </url>
        @endforeach
    @endforeach
</urlset>