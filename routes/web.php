<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Public\Home;
use App\Livewire\Public\Rankings;
use App\Livewire\Public\BeachDetail;
use App\Livewire\Account\Profile;
use App\Livewire\Admin\Dashboard;
use App\Http\Controllers\Auth\GoogleAuthController;

Route::pattern('locale', implode('|', config('locales.supported', ['pt', 'en', 'es', 'fr'])));

Route::post('/locale/{locale}', function ($locale) {
    $supported = config('locales.supported', ['pt', 'en', 'es', 'fr']);
    $default = config('locales.default', 'pt');

    if (!in_array($locale, $supported)) {
        $locale = $default;
    }

    session(['locale' => $locale]);
    app()->setLocale($locale);

    if (auth()->check()) {
        auth()->user()->update(['locale' => $locale]);
    }

    $referrer = request()->headers->get('referer');
    if ($referrer) {
        $parsedUrl = parse_url($referrer);
        $path = $parsedUrl['path'] ?? '';
        
        // Don't translate internal / debug / auth paths
        if (
            !str_starts_with($path, '/api') &&
            !str_starts_with($path, '/livewire') &&
            !str_starts_with($path, '/filament') &&
            !str_starts_with($path, '/_debugbar') &&
            !str_starts_with($path, '/auth') &&
            !str_contains($path, '.')
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
                    // The URL is already localized with a prefix.
                    $segments[0] = $locale;
                    
                    if (isset($segments[1]) && in_array($segments[1], $beachPrefixes)) {
                        $segments[1] = $beachPrefixes[$locale];
                    }
                    
                    $newPath = '/' . implode('/', $segments);
                } else {
                    // The URL is NOT localized with a prefix.
                    if (in_array($segments[0], $beachPrefixes)) {
                        $segments[0] = $beachPrefixes[$locale];
                    }
                    $newPath = '/' . $locale . '/' . implode('/', $segments);
                }
            } else {
                $newPath = '/' . $locale;
            }
            
            if (isset($parsedUrl['query'])) {
                $newPath .= '?' . $parsedUrl['query'];
            }
            
            return redirect($newPath);
        }
    }

    return redirect()->back();
})->name('locale.switch');

Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

Route::view('/offline', 'public.offline')->name('offline');
Route::view('/sobre', 'public.about')->name('about');
Route::view('/contactos', 'public.contact')->name('contact');
Route::view('/termos', 'public.terms')->name('terms');
Route::view('/privacidade', 'public.privacy')->name('privacy');

Route::prefix('push')->middleware('auth')->group(function () {
    Route::post('/subscribe', [\App\Http\Controllers\PushSubscriptionController::class, 'subscribe'])->name('push.subscribe');
    Route::post('/unsubscribe', [\App\Http\Controllers\PushSubscriptionController::class, 'unsubscribe'])->name('push.unsubscribe');
    Route::get('/status', [\App\Http\Controllers\PushSubscriptionController::class, 'status'])->name('push.status');
    Route::post('/test', [\App\Http\Controllers\PushSubscriptionController::class, 'test'])->name('push.test');
});

Route::get('/', Home::class)->name('home');
Route::get('/rankings', Rankings::class)->name('rankings');
Route::get('/area-pessoal', Profile::class)->name('profile')->middleware('auth');

Route::prefix('{locale}')->group(function () {
    Route::get('/', Home::class)->name('home.locale');
    Route::get('/rankings', Rankings::class)->name('rankings.locale');
    Route::view('/sobre', 'public.about')->name('about.locale');
    Route::view('/contactos', 'public.contact')->name('contact.locale');
    Route::view('/termos', 'public.terms')->name('terms.locale');
    Route::view('/privacidade', 'public.privacy')->name('privacy.locale');
    Route::get('/area-pessoal', Profile::class)->name('profile.locale')->middleware('auth');

    Route::get('/praias/{slug}', BeachDetail::class)->name('beach.show.pt');
    Route::get('/beaches/{slug}', BeachDetail::class)->name('beach.show.en');
    Route::get('/playas/{slug}', BeachDetail::class)->name('beach.show.es');
    Route::get('/plages/{slug}', BeachDetail::class)->name('beach.show.fr');

    Route::get('/admin-dashboard', Dashboard::class)->name('admin.dashboard')->middleware(['auth', 'admin']);
});

Route::get('/sitemap.xml', function () {
    $beaches = \App\Models\Beach::select('slug', 'updated_at')->get();

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
})->name('sitemap');

Route::get('/ads.txt', function () {
    $id = config('ads.publisher_id');
    if ($id) {
        return response("google.com, pub-{$id}, DIRECT, f08c47fec0942fa0\n", 200, ['Content-Type' => 'text/plain']);
    }
    return response('', 404);
});

Route::post('/logout', function (\Illuminate\Http\Request $request) {
    $logout = new \App\Livewire\Actions\Logout();
    $logout();
    return redirect()->route('home');
})->name('logout')->middleware('auth');

require __DIR__.'/auth.php';
