<?php

namespace App\Http\Controllers;

use App\Models\Beach;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $beaches = Beach::select('slug', 'updated_at')->get();

        $segments = [];
        foreach (config('locales.supported', ['pt', 'en', 'es', 'fr']) as $locale) {
            $segments[$locale] = match ($locale) {
                'en' => 'beaches',
                'es' => 'playas',
                'fr' => 'plages',
                default => 'praias',
            };
        }

        return response()->view('sitemap', [
            'locales' => $segments,
            'beaches' => $beaches,
            'staticPages' => [
                ['loc' => url('/'), 'priority' => '1.0', 'changefreq' => 'hourly'],
                ['loc' => url('/rankings'), 'priority' => '0.8', 'changefreq' => 'daily'],
                ['loc' => url('/sobre'), 'priority' => '0.6', 'changefreq' => 'monthly'],
                ['loc' => url('/contactos'), 'priority' => '0.5', 'changefreq' => 'monthly'],
                ['loc' => url('/termos'), 'priority' => '0.3', 'changefreq' => 'yearly'],
                ['loc' => url('/privacidade'), 'priority' => '0.3', 'changefreq' => 'yearly'],
            ],
        ])->header('Content-Type', 'text/xml');
    }
}
