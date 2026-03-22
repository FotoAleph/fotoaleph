<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SocialNetworkController extends Controller
{
    /**
     * Get social networks for a model.
     */
    public function index(string $socialable_type, int $socialable_id): JsonResponse
    {
        $modelClass = match ($socialable_type) {
            'tenant' => Tenant::class,
            'user' => User::class,
            default => null,
        };

        if (!$modelClass) {
            return response()->json(['error' => 'Tipo de modelo no válido'], 400);
        }

        $modelInstance = $modelClass::find($socialable_id);

        if (!$modelInstance) {
            return response()->json(['error' => 'Modelo no encontrado'], 404);
        }

        return response()->json($modelInstance->redesSociales()->with('socialNetworkType')->get());
    }

    /**
     * Get random social networks for a model.
     */
    public function random(string $socialable_type, int $socialable_id): JsonResponse
    {
        $modelClass = match ($socialable_type) {
            'tenant' => Tenant::class,
            'user' => User::class,
            default => null,
        };

        if (!$modelClass) {
            return response()->json(['error' => 'Tipo de modelo no válido'], 400);
        }

        $modelInstance = $modelClass::find($socialable_id);

        if (!$modelInstance) {
            return response()->json(['error' => 'Modelo no encontrado'], 404);
        }

        return response()->json($modelInstance->aleatoriasRedesSociales()->with('socialNetworkType')->get());
    }
}