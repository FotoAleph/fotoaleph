<?php

use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\PqrController;
use App\Http\Controllers\TenantController;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::resource('tenants', TenantController::class);
    Route::resource('pqrs', PqrController::class);
    Route::resource('cotizaciones', CotizacionController::class);
});

require __DIR__.'/settings.php';
