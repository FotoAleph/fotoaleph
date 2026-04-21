<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Multimedia;
use App\Models\Proyecto;
use App\Support\Api\IntegerCounterMutation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class JymCatalogController extends Controller
{
    public function index(): JsonResponse
    {
        $proyectos = Proyecto::query()->with(['categoria', 'grupo', 'multimedias'])->get();

        return response()->json($proyectos->map(function (Proyecto $proyecto) {
            return [
                'id' => $proyecto->id,
                'name' => $proyecto->nombre,
                'description' => $proyecto->descripcion,
                'category' => $proyecto->categoria?->nombre,
                'group' => $proyecto->grupo?->nombre,
                'materiales' => $proyecto->materiales ?? [],
                'multimedias' => $proyecto->imaganes->take(4)->map(function (Multimedia $media) {
                    return [
                        'id' => $media->id,
                        'img' => $media->preview_url ?: $media->url,
                        'img_detail' => $media->url,
                        'media_type' => $media->type,
                        'orientacion' => $media->orientacion,
                        'date' => $media->created_at?->toISOString(),
                        'level' => (int) ($media->nivel ?? 0),
                    ];
                }) ,
            ];
        }));
    }

    public function show(Proyecto $proyecto): JsonResponse
    {
        $proyecto->load(['categoria', 'grupo', 'multimedias']);

        return response()->json([
            'id' => $proyecto->id,
            'name' => $proyecto->nombre,
            'description' => $proyecto->descripcion,
             'category' => $proyecto->categoria?->nombre,
                'group' => $proyecto->grupo?->nombre,
                'materiales' => $proyecto->materiales ?? [],
                'multimedias' => $proyecto->multimedias->map(function (Multimedia $media) {
                    return [
                        'id' => $media->id,
                        'img' => $media->preview_url ?: $media->url,
                        'img_detail' => $media->url,
                        'media_type' => $media->type,
                        'orientacion' => $media->aspect_ratio,
                        'date' => $media->created_at?->toISOString(),
                        'level' => (int) ($media->nivel ?? 0),
                    ];
                }) ,
        ]);
    }

    public function updateLevel(Request $request, int $multimedia, IntegerCounterMutation $mutation): JsonResponse
    {
        $validated = $request->validate([
            'operation' => ['required', 'in:increment,decrement,set'],
            'value' => ['required', 'integer', 'min:0'],
        ]);

        $media = Multimedia::on('tenant_jym')
            ->whereHas('proyectos')
            ->findOrFail($multimedia);

        $level = $mutation->apply($media, 'nivel', $validated['operation'], (int) $validated['value']);

        return response()->json([
            'id' => $media->id,
            'level' => $level,
        ]);
    }

    public function indexByGroup(): JsonResponse
    {
            $grupos = Proyecto::whereNotNull('grupo_id')->with(['categoria', 'grupo', 'multimedias'])->get()
            ->groupBy('grupo_id');
            return response()->json($grupos->map(function ($proyectos, $grupoId) {
                $grupoName = $proyectos->first()->grupo?->nombre ?? 'Sin categoría';
                $grupoDescription = $proyectos->first()->grupo?->descripcion ?? 'Sin descripción';
                return [
                    'id' => $grupoId,
                    'name' => $grupoName,
                    'description' => $grupoDescription,
                    'proyectos' => $proyectos->take(5)->map(function (Proyecto $proyecto) {
                        return [
                            'id' => $proyecto->id,
                            'name' => $proyecto->nombre,
                            'description' => $proyecto->descripcion,
                            'category' => $proyecto->grupo?->nombre,
                            'group' => $proyecto->grupo?->nombre,
                            'materiales' => $proyecto->materiales ?? [],
                            'multimedias' => $proyecto->imaganes->take(3)->map(function (Multimedia $media) {
                                return [
                                    'id' => $media->id,
                                    'img' => $media->preview_url ?: $media->url,
                                    'img_detail' => $media->url,
                                    'media_type' => $media->type,
                                    'orientacion' => $media->orientacion,
                                    'date' => $media->created_at?->toISOString(),
                                    'level' => (int) ($media->nivel ?? 0),
                                ];
                            }),
                        ];
                    })->values(),
                ];
            })->values());

    }

    public function showByGroup(string $grupo): JsonResponse
    {
        $proyectos = Proyecto::where('grupo_id', $grupo)->with(['categoria', 'grupo', 'multimedias'])->get();
        return response()->json($proyectos->map(function (Proyecto $proyecto) {
            return [
                'id' => $proyecto->id,
                'name' => $proyecto->nombre,
                'description' => $proyecto->descripcion,
                'category' => $proyecto->categoria?->nombre,
                'group' => $proyecto->grupo?->nombre,
                'materiales' => $proyecto->materiales ?? [],
                'multimedias' => $proyecto->multimedias->take(6),
            ];
        }));
    }
    public function indexByCategory(): JsonResponse
    {
           $categorias = Proyecto::whereNotNull('categoria_id')->with(['categoria', 'grupo', 'multimedias'])->get()
            ->groupBy('categoria_id');
            return response()->json($categorias->map(function ($proyectos, $categoriaId) {
                $categoriaName = $proyectos->first()->categoria?->nombre ?? 'Sin categoría';
                $categoriaDescription = $proyectos->first()->categoria?->descripcion ?? 'Sin descripción';
                return [
                    'id' => $categoriaId,
                    'name' => $categoriaName,
                    'description' => $categoriaDescription,
                    'proyectos' => $proyectos->take(5)->map(function (Proyecto $proyecto) {
                        return [
                            'id' => $proyecto->id,
                            'name' => $proyecto->nombre,
                            'description' => $proyecto->descripcion,
                            'category' => $proyecto->categoria?->nombre,
                            'group' => $proyecto->grupo?->nombre,
                            'materiales' => $proyecto->materiales ?? [],
                            'multimedias' => $proyecto->imaganes->take(3)->map(function (Multimedia $media) {
                                return [
                                    'id' => $media->id,
                                    'img' => $media->preview_url ?: $media->url,
                                    'img_detail' => $media->url,
                                    'media_type' => $media->type,
                                    'orientacion' => $media->orientacion,
                                    'date' => $media->created_at?->toISOString(),
                                    'level' => (int) ($media->nivel ?? 0),
                                ];
                            }),
                        ];
                    })->values(),
                ];
            })->values());

    }

    public function showByCategory(string $categoria): JsonResponse
    {
        $proyectos = Proyecto::where('categoria_id', $categoria)->with(['categoria', 'grupo', 'multimedias'])->get();

        return response()->json($proyectos->map(function (Proyecto $proyecto) {
            return [
                'id' => $proyecto->id,
                'name' => $proyecto->nombre,
                'description' => $proyecto->descripcion,
                'category' => $proyecto->categoria?->nombre,
                'group' => $proyecto->grupo?->nombre,
                'materiales' => $proyecto->materiales ?? [],
                'multimedias' => $proyecto->multimedias->take(6)->map(function (Multimedia $media) {
                    return [
                        'id' => $media->id,
                        'img' => $media->preview_url ?: $media->url,
                        'img_detail' => $media->url,
                        'media_type' => $media->type,
                        'orientacion' => $media->orientacion,
                        'date' => $media->created_at?->toISOString(),
                        'level' => (int) ($media->nivel ?? 0),
                    ];
                }) ,
            ];
        }));
    }

    
    private function projectRows(iterable $projects): Collection
    {
        return collect($projects)
            ->flatMap(function (Proyecto $proyecto) {
                return $proyecto->multimedias->map(function (Multimedia $media) use ($proyecto) {
                    return [
                        'id' => $media->id,
                        'proyecto' => (string) $proyecto->id,
                        'img' => $media->preview_url ?: $media->url,
                        'img_detail' => $media->url,
                        'media_type' => $media->type,
                        'orientacion' => $media->orientacion,
                        'category' => $proyecto->categoria?->nombre,
                        'group' => $proyecto->grupo?->nombre,
                        'materiales' => $proyecto->materiales ?? [],
                        'name' => $proyecto->nombre,
                        'description' => $proyecto->descripcion,
                        'date' => $media->created_at?->toISOString(),
                        'level' => (int) ($media->nivel ?? 0),
                    ];
                });
            });
    }
}