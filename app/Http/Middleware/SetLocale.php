<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $supportedLocales = ['pt', 'en', 'es', 'fr'];
        $firstSegment = $request->segment(1);

        // Check if the route is one of the administrative, API or Livewire paths to skip redirecting
        $path = $request->path();
        if (
            $request->is('api/*') || 
            $request->is('livewire/*') || 
            $request->is('filament/*') || 
            $request->is('_debugbar/*') || 
            $request->is('auth/*') || 
            str_contains($path, '.') // skips files like favicon.ico, manifest.json
        ) {
            $locale = session('locale');
            if (!$locale || !in_array($locale, $supportedLocales)) {
                $locale = $request->getPreferredLanguage($supportedLocales) ?: 'pt';
            }
            App::setLocale($locale);
            URL::defaults(['locale' => $locale]);
            return $next($request);
        }

        if (in_array($firstSegment, $supportedLocales)) {
            $locale = $firstSegment;
        } else {
            $locale = session('locale');
            if (!$locale || !in_array($locale, $supportedLocales)) {
                $locale = $request->getPreferredLanguage($supportedLocales) ?: 'pt';
            }
        }

        App::setLocale($locale);
        URL::defaults(['locale' => $locale]);
        session(['locale' => $locale]);

        return $next($request);
    }
}
