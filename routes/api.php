<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SocialNetworkController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');

// Social networks routes
Route::get('/redes-sociales/{socialable_type}/{socialable_id}', [SocialNetworkController::class, 'index']);
Route::get('/redes-sociales/{socialable_type}/{socialable_id}/aleatorias', [SocialNetworkController::class, 'random']);



Route::get('/direcciones/{direccionable_type}/{direccionable_id}', function ($direccionable_type, $direccionable_id) {
    $modelClass = match ($direccionable_type) {
        'tenant' => \App\Models\Tenant::class,
        'user' => \App\Models\User::class,
        default => null,
    };

    if (!$modelClass) {
        return response()->json(['error' => 'Tipo de modelo no válido'], 400);
    }

    $modelInstance = $modelClass::find($direccionable_id);

    if (!$modelInstance) {
        return response()->json(['error' => 'Modelo no encontrado'], 404);
    }

    return response()->json($modelInstance->direcciones);
});