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
    public function index(Request $request): JsonResponse
    {
        $rows = $this->projectRows(
            Proyecto::query()->with(['categoria', 'grupo', 'multimedias'])
                ->get()
        );

        if ($request->filled('category')) {
            $rows = $rows->where('category', (string) $request->string('category'));
        }

        if ($request->filled('group')) {
            $rows = $rows->where('group', (string) $request->string('group'));
        }

        if ($request->filled('material')) {
            $material = (string) $request->string('material');
            $rows = $rows->filter(fn (array $row) => in_array($material, $row['materiales'], true));
        }

        $orderBy = $request->string('ordenar_por', 'level')->toString();
        $direction = strtolower($request->string('direccion', 'desc')->toString()) === 'asc';

        $rows = $orderBy === 'date'
            ? $rows->sortBy('date', options: SORT_REGULAR, descending: ! $direction)
            : $rows->sortBy('level', options: SORT_REGULAR, descending: ! $direction);

        return response()->json($rows->values());
    }

    public function show(Proyecto $proyecto): JsonResponse
    {
        $proyecto->load(['categoria', 'grupo', 'multimedias']);

        $sameGroup = $proyecto->grupo_id
            ? Proyecto::query()->with(['categoria', 'grupo', 'multimedias'])->where('grupo_id', $proyecto->grupo_id)->get()
            : collect();

        $sameCategory = $proyecto->categoria_id
            ? Proyecto::query()->with(['categoria', 'grupo', 'multimedias'])->where('categoria_id', $proyecto->categoria_id)->get()
            : collect();

        return response()->json([
            'project' => [
                'id' => $proyecto->id,
                'name' => $proyecto->nombre,
                'description' => $proyecto->descripcion,
                'category' => $proyecto->categoria?->nombre,
                'group' => $proyecto->grupo?->nombre,
                'materiales' => $proyecto->materiales ?? [],
            ],
            'mismo_proyecto' => $this->projectRows(collect([$proyecto]))->values(),
            'mismo_grupo' => $this->projectRows($sameGroup)->values(),
            'misma_categoria' => $this->projectRows($sameCategory)->values(),
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