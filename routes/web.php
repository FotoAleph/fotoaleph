<?php

use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PqrController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WelcomeController;
use Laravel\Fortify\Features;

Route::get('/', [WelcomeController::class, 'index'])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('tenants', TenantController::class);
    Route::resource('pqrs', PqrController::class);
    Route::resource('cotizaciones', CotizacionController::class);
    Route::resource('users', UserController::class);
    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
});

require __DIR__.'/settings.php';
