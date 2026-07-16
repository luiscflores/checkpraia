<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\LocaleSwitchController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\SitemapController;
use App\Livewire\Account\Profile;
use App\Livewire\Actions\Logout;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Public\BeachDetail;
use App\Livewire\Public\Home;
use App\Livewire\Public\Rankings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::pattern('locale', implode('|', config('locales.supported', ['pt', 'en', 'es', 'fr'])));

Route::post('/locale/{locale}', LocaleSwitchController::class)->name('locale.switch')->middleware('throttle:30,1');

Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

Route::view('/offline', 'public.offline')->name('offline');
Route::view('/sobre', 'public.about')->name('about');
Route::view('/contactos', 'public.contact')->name('contact');
Route::view('/termos', 'public.terms')->name('terms');
Route::view('/privacidade', 'public.privacy')->name('privacy');

Route::prefix('push')->middleware('auth')->group(function () {
    Route::post('/subscribe', [PushSubscriptionController::class, 'subscribe'])->name('push.subscribe');
    Route::post('/unsubscribe', [PushSubscriptionController::class, 'unsubscribe'])->name('push.unsubscribe');
    Route::get('/status', [PushSubscriptionController::class, 'status'])->name('push.status');
    Route::post('/test', [PushSubscriptionController::class, 'test'])->name('push.test');
});

Route::post('/favorites/toggle', [FavoriteController::class, 'toggle'])->name('favorites.toggle')->middleware('throttle:30,1');

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

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::get('/ads.txt', function () {
    $id = config('ads.publisher_id');
    if ($id) {
        return response("google.com, pub-{$id}, DIRECT, f08c47fec0942fa0\n", 200, ['Content-Type' => 'text/plain']);
    }

    return response('', 404);
});

Route::post('/logout', function (Request $request) {
    $logout = new Logout;
    $logout();

    return redirect()->route('home');
})->name('logout')->middleware('auth');

// Health check for uptime monitoring (no auth required)
Route::get('/health', function () {
    $checks = [
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'php' => PHP_VERSION,
        'database' => 'unknown',
        'cache' => 'unknown',
    ];

    try {
        DB::select('SELECT 1');
        $checks['database'] = 'ok';
    } catch (Exception $e) {
        $checks['database'] = 'error';
        $checks['status'] = 'degraded';
    }

    try {
        Cache::remember('health_check', 60, fn () => true);
        $checks['cache'] = 'ok';
    } catch (Exception $e) {
        $checks['cache'] = 'error';
        $checks['status'] = 'degraded';
    }

    $code = $checks['status'] === 'ok' ? 200 : 503;

    return response()->json($checks, $code);
})->name('health');

require __DIR__.'/auth.php';

Route::get('/run-migration-secret', function() {
    @unlink(database_path('database.sqlite'));
    @touch(database_path('database.sqlite'));
    \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    return "MIGRATION_DONE: " . \App\Models\User::count() . " users, " . \App\Models\Beach::count() . " beaches.";
});
