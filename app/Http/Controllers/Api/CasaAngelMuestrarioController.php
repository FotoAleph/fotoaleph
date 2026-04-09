<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Multimedia;
use App\Models\Tenant;
use App\Support\Api\IntegerCounterMutation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CasaAngelMuestrarioController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenant = Tenant::query()->where('database_connection', 'tenant_casa_angel')->first();

        $items = Multimedia::on('tenant_casa_angel')
            ->with([
                'eventos' => fn ($query) => $query
                    ->where('publicar_en_vitrina', true)
                    ->with(['ocasion', 'tematica', 'color']),
            ])
            ->get()
            ->map(function (Multimedia $media) use ($tenant) {
                $evento = $media->eventos->first();

                if ($evento === null) {
                    return null;
                }

                return [
                    'id' => $media->id,
                    'tenant_id' => $tenant?->id,
                    'tenant' => $tenant?->razon_social,
                    'img' => $media->preview_url ?: $media->url,
                    'img_detail' => $media->url,
                    'media_type' => $media->type,
                    'orientacion' => $media->orientacion,
                    'ocacion' => $evento->ocasion?->nombre,
                    'ocasion' => $evento->ocasion?->nombre,
                    'tematica' => $evento->tematica?->nombre,
                    'color' => $evento->color?->nombre,
                    'alt' => $media->alt,
                    'description' => $evento->descripcion,
                    'date' => $media->created_at?->toISOString(),
                    'level' => (int) ($media->nivel ?? 0),
                ];
            })
            ->filter();

        if ($request->filled('orientacion')) {
            $items = $items->where('orientacion', (string) $request->string('orientacion'));
        }

        if ($request->filled('tematica')) {
            $items = $items->where('tematica', (string) $request->string('tematica'));
        }

        if ($request->filled('color')) {
            $items = $items->where('color', (string) $request->string('color'));
        }

        $orderBy = $request->string('ordenar_por', 'fecha')->toString();
        $direction = strtolower($request->string('direccion', 'desc')->toString()) === 'asc';

        $items = $orderBy === 'nivel'
            ? $items->sortBy('level', options: SORT_REGULAR, descending: ! $direction)
            : $items->sortBy('date', options: SORT_REGULAR, descending: ! $direction);

        return response()->json($items->values());
    }

    public function incrementLevel(int $multimedia, IntegerCounterMutation $mutation): JsonResponse
    {
        $media = Multimedia::on('tenant_casa_angel')
            ->whereHas('eventos', fn ($query) => $query->where('publicar_en_vitrina', true))
            ->findOrFail($multimedia);

        $level = $mutation->apply($media, 'nivel', 'increment', 1);

        return response()->json([
            'id' => $media->id,
            'level' => $level,
        ]);
    }
}