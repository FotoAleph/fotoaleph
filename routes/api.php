<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/redes-sociales/{socialable_type}/{socialable_id}', function ($socialable_type, $socialable_id) {
    $modelClass = match ($socialable_type) {
        'tenant' => \App\Models\Tenant::class,
        'user' => \App\Models\User::class,
        default => null,
    };

    if (!$modelClass) {
        return response()->json(['error' => 'Tipo de modelo no válido'], 400);
    }

    $modelInstance = $modelClass::find($socialable_id);

    if (!$modelInstance) {
        return response()->json(['error' => 'Modelo no encontrado'], 404);
    }

    return response()->json($modelInstance->redesSociales);
});

Route::get('/redes-sociales/{socialable_type}/{socialable_id}/aleatorias', function ($socialable_type, $socialable_id) {
    $modelClass = match ($socialable_type) {
        'tenant' => \App\Models\Tenant::class,
        'user' => \App\Models\User::class,
        default => null,
    };

    if (!$modelClass) {
        return response()->json(['error' => 'Tipo de modelo no válido'], 400);
    }

    $modelInstance = $modelClass::find($socialable_id);

    if (!$modelInstance) {
        return response()->json(['error' => 'Modelo no encontrado'], 404);
    }

    return response()->json($modelInstance->aleatoriasRedesSociales);
});


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