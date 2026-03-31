<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sitio;
use App\Models\Tenant;
use App\Models\Vitrina;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PublicTenantVitrinaController extends Controller
{
    public function byTenant(Request $request, Tenant $tenant): JsonResponse
    {
        return response()->json(
            $this->transform(
                Vitrina::filtrar(
                    $this->filters($request),
                    $tenant->vitrinas()->with(['tenant', 'categoria', 'grupo', 'nivel'])->get(),
                ),
            ),
        );
    }

    public function bySite(Request $request, string $site): JsonResponse
    {
        $sitio = Sitio::query()
            ->with('tenant')
            ->where('url', $site)
            ->orWhere('name', $site)
            ->firstOrFail();

        return $this->byTenant($request, $sitio->tenant);
    }

    private function filters(Request $request): array
    {
        return [
            'categoria' => $request->query('categoria'),
            'grupo' => $request->query('grupo'),
            'direccion_fecha' => $request->query('direccion_fecha', 'desc'),
            'direccion_nivel' => $request->query('direccion_nivel', 'asc'),
        ];
    }

    private function transform(Collection $vitrinas): Collection
    {
        return $vitrinas->map(fn (Vitrina $vitrina) => [
            'id' => $vitrina->id,
            'tenant_id' => $vitrina->tenant_id,
            'tenant' => $vitrina->tenant?->razon_social,
            'img' => $vitrina->imagen,
            'category' => $vitrina->categoria?->nombre,
            'group' => $vitrina->grupo?->nombre,
            'name' => $vitrina->nombre,
            'description' => $vitrina->descripcion,
            'date' => $vitrina->created_at?->toISOString(),
            'level' => $vitrina->nivel?->nivel,
        ])->values();
    }
}
