<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Multimedia;
use App\Models\Proyecto;
use App\Models\Sitio;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicTenantProjectController extends Controller
{
    public function byTenant(Request $request, Tenant $tenant): JsonResponse
    {
        abort_unless($tenant->databaseConnectionName() === 'tenant_jym', 404);

        $query = Proyecto::query()->with(['categoria', 'grupo', 'multimedias']);

        if ($request->filled('categoria')) {
            $query->whereHas('categoria', fn ($categoriaQuery) => $categoriaQuery->where('nombre', $request->string('categoria')));
        }

        if ($request->filled('grupo')) {
            $query->whereHas('grupo', fn ($grupoQuery) => $grupoQuery->where('nombre', $request->string('grupo')));
        }

        return response()->json(
            $query->latest()->get()->map(fn (Proyecto $proyecto) => [
                'id' => $proyecto->id,
                'tenant' => $tenant->razon_social,
                'nombre' => $proyecto->nombre,
                'descripcion' => $proyecto->descripcion,
                'categoria' => $proyecto->categoria?->nombre,
                'grupo' => $proyecto->grupo?->nombre,
                'publicar_en_vitrina' => (bool) $proyecto->publicar_en_vitrina,
                'cover_url' => $proyecto->primaryMedia()?->preview_url ?? $proyecto->primaryMedia()?->url,
                'media' => $proyecto->multimedias->map(fn (Multimedia $media) => [
                    'url' => $media->url,
                    'preview_url' => $media->preview_url,
                    'type' => $media->type,
                ])->values(),
            ])->values(),
        );
    }

    public function bySite(Request $request, string $site): JsonResponse
    {
        $sitio = Sitio::query()->with('tenant')->where('url', $site)->orWhere('name', $site)->firstOrFail();

        return $this->byTenant($request, $sitio->tenant);
    }
}