<?php

use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PqrController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\WelcomeController;
use Laravel\Fortify\Features;

Route::get('/', [WelcomeController::class, 'index'])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('tenants', TenantController::class);
    Route::resource('pqrs', PqrController::class);
    Route::resource('cotizaciones', CotizacionController::class);
});

require __DIR__.'/settings.php';
