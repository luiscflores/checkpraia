<?php

use Illuminate\Support\Facades\Route;

Route::pattern('locale', 'pt|en|es|fr');

Route::prefix('{locale}')->group(function () {
    Route::get('/', \App\Livewire\Public\Home::class)->name('home');
    Route::get('/rankings', \App\Livewire\Public\Rankings::class)->name('rankings');
    Route::get('/area-pessoal', \App\Livewire\Account\Profile::class)->name('profile'); // Auth check done within or via middleware

    // Beach Details (aliased per language)
    Route::get('/praias/{slug}', \App\Livewire\Public\BeachDetail::class)->name('beach.show.pt');
    Route::get('/beaches/{slug}', \App\Livewire\Public\BeachDetail::class)->name('beach.show.en');
    Route::get('/playas/{slug}', \App\Livewire\Public\BeachDetail::class)->name('beach.show.es');
    Route::get('/plages/{slug}', \App\Livewire\Public\BeachDetail::class)->name('beach.show.fr');

    // Admin Backoffice Dashboard
    Route::get('/admin-dashboard', \App\Livewire\Admin\Dashboard::class)->name('admin.dashboard');
});
