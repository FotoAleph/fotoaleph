<?php

use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\BiotekEstudianteController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EstudianteController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\PqrController;
use App\Http\Controllers\SitioController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\TenantEventoController;
use App\Http\Controllers\TenantProyectoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WelcomeController;
use Laravel\Fortify\Features;

Route::get('/', [WelcomeController::class, 'index'])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('estudiantes/descargar/todas', [EstudianteController::class, 'downloadAll'])->name('estudiantes.download-all');
    Route::get('estudiantes/{estudiante}/descargar', [EstudianteController::class, 'download'])->name('estudiantes.download');

    Route::resource('tenants', TenantController::class);
    Route::resource('pqrs', PqrController::class);
    Route::resource('cotizaciones', CotizacionController::class);
    Route::resource('users', UserController::class);
    Route::resource('sitios', SitioController::class);
    Route::resource('estudiantes', EstudianteController::class)->except(['show']);
    Route::resource('grupos', GrupoController::class);
    Route::resource('categorias', CategoriaController::class);
    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');

    Route::get('tenants/{tenant}/proyectos', [TenantProyectoController::class, 'index'])->name('tenant-projects.index');
    Route::get('tenants/{tenant}/proyectos/create', [TenantProyectoController::class, 'create'])->name('tenant-projects.create');
    Route::post('tenants/{tenant}/proyectos', [TenantProyectoController::class, 'store'])->name('tenant-projects.store');
    Route::get('tenants/{tenant}/proyectos/{proyecto}/edit', [TenantProyectoController::class, 'edit'])->name('tenant-projects.edit');
    Route::match(['put', 'patch'], 'tenants/{tenant}/proyectos/{proyecto}', [TenantProyectoController::class, 'update'])->name('tenant-projects.update');
    Route::delete('tenants/{tenant}/proyectos/{proyecto}', [TenantProyectoController::class, 'destroy'])->name('tenant-projects.destroy');

    Route::get('tenants/{tenant}/eventos', [TenantEventoController::class, 'index'])->name('tenant-events.index');
    Route::get('tenants/{tenant}/eventos/create', [TenantEventoController::class, 'create'])->name('tenant-events.create');
    Route::post('tenants/{tenant}/eventos', [TenantEventoController::class, 'store'])->name('tenant-events.store');
    Route::get('tenants/{tenant}/eventos/{evento}/edit', [TenantEventoController::class, 'edit'])->name('tenant-events.edit');
    Route::match(['put', 'patch'], 'tenants/{tenant}/eventos/{evento}', [TenantEventoController::class, 'update'])->name('tenant-events.update');
    Route::delete('tenants/{tenant}/eventos/{evento}', [TenantEventoController::class, 'destroy'])->name('tenant-events.destroy');

    Route::get('tenants/{tenant}/biotek/estudiantes', [BiotekEstudianteController::class, 'index'])->name('biotek-students.index');
    Route::get('tenants/{tenant}/biotek/estudiantes/create', [BiotekEstudianteController::class, 'create'])->name('biotek-students.create');
    Route::post('tenants/{tenant}/biotek/estudiantes', [BiotekEstudianteController::class, 'store'])->name('biotek-students.store');
    Route::get('tenants/{tenant}/biotek/estudiantes/{biotekEstudiante}/edit', [BiotekEstudianteController::class, 'edit'])->name('biotek-students.edit');
    Route::match(['put', 'patch'], 'tenants/{tenant}/biotek/estudiantes/{biotekEstudiante}', [BiotekEstudianteController::class, 'update'])->name('biotek-students.update');
    Route::delete('tenants/{tenant}/biotek/estudiantes/{biotekEstudiante}', [BiotekEstudianteController::class, 'destroy'])->name('biotek-students.destroy');
});

require __DIR__.'/settings.php';
