<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleSwitchController extends Controller
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        $supported = config('locales.supported', ['pt', 'en', 'es', 'fr']);
        $default = config('locales.default', 'pt');

        if (! in_array($locale, $supported)) {
            $locale = $default;
        }

        session(['locale' => $locale]);
        app()->setLocale($locale);

        if (auth()->check()) {
            auth()->user()->update(['locale' => $locale]);
        }

        $referrer = $request->headers->get('referer');
        if ($referrer) {
            $parsedUrl = parse_url($referrer);
            $path = $parsedUrl['path'] ?? '';

            if (
                ! str_starts_with($path, '/api') &&
                ! str_starts_with($path, '/livewire') &&
                ! str_starts_with($path, '/filament') &&
                ! str_starts_with($path, '/_debugbar') &&
                ! str_starts_with($path, '/auth') &&
                ! str_contains($path, '.')
            ) {
                $trimmedPath = trim($path, '/');
                $segments = explode('/', $trimmedPath);

                $beachPrefixes = [
                    'en' => 'beaches',
                    'es' => 'playas',
                    'fr' => 'plages',
                    'pt' => 'praias',
                ];

                if (count($segments) > 0 && $segments[0] !== '') {
                    if (in_array($segments[0], $supported)) {
                        $segments[0] = $locale;

                        if (isset($segments[1]) && in_array($segments[1], $beachPrefixes)) {
                            $segments[1] = $beachPrefixes[$locale];
                        }

                        $newPath = '/'.implode('/', $segments);
                    } else {
                        if (in_array($segments[0], $beachPrefixes)) {
                            $segments[0] = $beachPrefixes[$locale];
                        }
                        $newPath = '/'.$locale.'/'.implode('/', $segments);
                    }
                } else {
                    $newPath = '/'.$locale;
                }

                if (isset($parsedUrl['query'])) {
                    $newPath .= '?'.$parsedUrl['query'];
                }

                return redirect($newPath);
            }
        }

        return redirect()->back();
    }
}
