<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Public\Home;
use App\Livewire\Public\Rankings;
use App\Livewire\Public\BeachDetail;
use App\Livewire\Account\Profile;
use App\Livewire\Admin\Dashboard;
use App\Http\Controllers\Auth\GoogleAuthController;

Route::pattern('locale', 'pt|en|es|fr');

Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

Route::get('/', Home::class)->name('home');
Route::get('/rankings', Rankings::class)->name('rankings');
Route::get('/area-pessoal', Profile::class)->name('profile');

Route::prefix('{locale}')->group(function () {
    Route::get('/', Home::class)->name('home.locale');
    Route::get('/rankings', Rankings::class)->name('rankings.locale');
    Route::get('/area-pessoal', Profile::class)->name('profile.locale');

    Route::get('/praias/{slug}', BeachDetail::class)->name('beach.show.pt');
    Route::get('/beaches/{slug}', BeachDetail::class)->name('beach.show.en');
    Route::get('/playas/{slug}', BeachDetail::class)->name('beach.show.es');
    Route::get('/plages/{slug}', BeachDetail::class)->name('beach.show.fr');

    Route::get('/admin-dashboard', Dashboard::class)->name('admin.dashboard');
});

require __DIR__.'/auth.php';
