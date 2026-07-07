<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $supportedLocales = config('locales.supported', ['pt', 'en', 'es', 'fr']);
        $defaultLocale = config('locales.default', 'pt');
        $firstSegment = $request->segment(1);

        $path = $request->path();
        if (
            $request->is('api/*') ||
            $request->is('livewire/*') ||
            $request->is('filament/*') ||
            $request->is('_debugbar/*') ||
            $request->is('auth/*') ||
            str_contains($path, '.')
        ) {
            $locale = $this->resolveLocale($request, $supportedLocales, $defaultLocale);
            App::setLocale($locale);
            URL::defaults(['locale' => $locale]);
            return $next($request);
        }

        if (in_array($firstSegment, $supportedLocales)) {
            $locale = $firstSegment;
        } else {
            $locale = $this->resolveLocale($request, $supportedLocales, $defaultLocale);
        }

        App::setLocale($locale);
        URL::defaults(['locale' => $locale]);
        session(['locale' => $locale]);

        if (auth()->check() && Schema::hasColumn('users', 'locale') && auth()->user()->locale !== $locale) {
            auth()->user()->updateQuietly(['locale' => $locale]);
        }

        return $next($request);
    }

    private function resolveLocale(Request $request, array $supportedLocales, string $defaultLocale): string
    {
        $locale = session('locale');

        if ($locale && in_array($locale, $supportedLocales)) {
            return $locale;
        }

        if (auth()->check() && Schema::hasColumn('users', 'locale')) {
            $userLocale = auth()->user()->locale;
            if ($userLocale && in_array($userLocale, $supportedLocales)) {
                return $userLocale;
            }
        }

        return $request->getPreferredLanguage($supportedLocales) ?: $defaultLocale;
    }
}
