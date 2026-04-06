<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ManagedTenantVitrinaController;
use App\Http\Controllers\Api\PublicTenantEventController;
use App\Http\Controllers\Api\PublicTenantProjectController;
use App\Http\Controllers\Api\PublicTenantVitrinaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SocialNetworkController;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');

// Social networks routes
Route::get('/redes-sociales/{socialable_type}/{socialable_id}', [SocialNetworkController::class, 'index']);
Route::get('/redes-sociales/{socialable_type}/{socialable_id}/aleatorias', [SocialNetworkController::class, 'random']);
Route::get('/vitrinas/tenant/{tenant}', [PublicTenantVitrinaController::class, 'byTenant'])->middleware('tenant.connection');
Route::get('/vitrinas/sitio/{site}', [PublicTenantVitrinaController::class, 'bySite'])->middleware('tenant.connection');
Route::get('/proyectos/tenant/{tenant}', [PublicTenantProjectController::class, 'byTenant']);
Route::get('/proyectos/sitio/{site}', [PublicTenantProjectController::class, 'bySite']);
Route::get('/eventos/tenant/{tenant}', [PublicTenantEventController::class, 'byTenant']);
Route::get('/eventos/sitio/{site}', [PublicTenantEventController::class, 'bySite']);

Route::get('/direcciones/{direccionable_type}/{direccionable_id}', function ($direccionable_type, $direccionable_id) {
    $modelClass = match ($direccionable_type) {
        'tenant' => Tenant::class,
        'user' => User::class,
        default => null,
    };

    if (! $modelClass) {
        return response()->json(['error' => 'Tipo de modelo no válido'], 400);
    }

    $modelInstance = $modelClass::find($direccionable_id);

    if (! $modelInstance) {
        return response()->json(['error' => 'Modelo no encontrado'], 404);
    }

    return response()->json($modelInstance->direcciones);
});

Route::middleware(['auth:sanctum', 'tenant.connection'])->group(function () {
    Route::get('/tenants/{tenant}/vitrinas', [ManagedTenantVitrinaController::class, 'index']);
    Route::post('/tenants/{tenant}/vitrinas', [ManagedTenantVitrinaController::class, 'store']);
    Route::get('/tenants/{tenant}/vitrinas/{vitrina}', [ManagedTenantVitrinaController::class, 'show']);
    Route::match(['put', 'patch'], '/tenants/{tenant}/vitrinas/{vitrina}', [ManagedTenantVitrinaController::class, 'update']);
    Route::delete('/tenants/{tenant}/vitrinas/{vitrina}', [ManagedTenantVitrinaController::class, 'destroy']);
});
