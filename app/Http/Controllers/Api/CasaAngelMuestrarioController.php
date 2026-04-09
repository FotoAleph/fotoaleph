<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CaMultimedia;
use App\Models\Muestra;
use App\Support\Api\IntegerCounterMutation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CasaAngelMuestrarioController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $muestrarios = Cache::remember(Muestra::apiCacheKey($request->query()), now()->addMinutes(10), function () {
            return Muestra::with('multimedia')->orderByDesc('nivel')->get()->map(function (Muestra $muestra) {
                $media = $muestra->multimedia;

                return [
                    'id' => $muestra->id,
                    'name' => $muestra->nombre,
                    'description' => $muestra->descripcion,
                    'nivel' => $muestra->nivel,
                    'multimedias' => $media ? [[
                        'id' => $media->id,
                        'url' => $media->url,
                        'preview_url' => $media->preview_url,
                        'type' => $media->type,
                        'aspect_ratio' => $media->aspect_ratio,
                        'alt' => $media->alt,
                    ]] : [],
                ];
            });
        });

        return response()->json($muestrarios);
    }

    public function incrementLevel(int $multimedia, IntegerCounterMutation $mutation): JsonResponse
    {
        $media = CaMultimedia::findOrFail($multimedia);
        $level = $mutation->apply($media, 'nivel', 'increment', 1);

        return response()->json([
            'id' => $media->id,
            'nivel' => $level,
        ]);
    }


}