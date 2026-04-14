<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ManagedTenantVitrinaController;
use App\Http\Controllers\Api\CasaAngelEventCatalogController;
use App\Http\Controllers\Api\CasaAngelMuestrarioController;
use App\Http\Controllers\Api\JymCatalogController;
use App\Http\Controllers\Api\PublicTenantEventController;
use App\Http\Controllers\Api\PublicTenantProjectController;
use App\Http\Controllers\Api\PublicTenantVitrinaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SocialNetworkController;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');


Route::get('/redes-sociales/{socialable_type}/{socialable_id}', [SocialNetworkController::class, 'index']);
Route::get('/redes-sociales/{socialable_type}/{socialable_id}/aleatorias', [SocialNetworkController::class, 'random']);
Route::get('/casa-angel/eventos', [CasaAngelEventCatalogController::class, 'index']);
Route::get('/casa-angel/muestrario', [CasaAngelMuestrarioController::class, 'index']);
Route::post('/casa-angel/muestrario/{multimedia}/level', [CasaAngelMuestrarioController::class, 'incrementLevel']);
Route::get('/jym/muestrario', [JymCatalogController::class, 'index']);
Route::get('/jym/proyectos/{proyecto}', [JymCatalogController::class, 'show']);
Route::patch('/jym/muestrario/{multimedia}/level', [JymCatalogController::class, 'updateLevel']);
Route::middleware(['auth:sanctum', 'tenant.connection'])->group(function () {
Route::get('/casa-angel/eventos/{evento}', [CasaAngelEventCatalogController::class, 'show']);
Route::patch('/casa-angel/eventos/{evento}/multimedia/{multimedia}/cantidad', [CasaAngelEventCatalogController::class, 'updateCantidad']);

});
