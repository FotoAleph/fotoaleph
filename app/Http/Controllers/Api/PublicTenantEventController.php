<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\Multimedia;
use App\Models\Sitio;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicTenantEventController extends Controller
{
    public function byTenant(Request $request, Tenant $tenant): JsonResponse
    {
        abort_unless($tenant->databaseConnectionName() === 'tenant_casa_angel', 404);

        return response()->json(
            Evento::query()
                ->with('multimedias')
                ->latest('fecha_evento')
                ->get()
                ->map(fn (Evento $evento) => [
                    'id' => $evento->id,
                    'tenant' => $tenant->razon_social,
                    'nombre' => $evento->nombre,
                    'descripcion' => $evento->descripcion,
                    'fecha_evento' => $evento->fecha_evento?->toISOString(),
                    'ubicacion' => $evento->ubicacion,
                    'codigo' => $evento->codigo,
                    'publicar_en_vitrina' => (bool) $evento->publicar_en_vitrina,
                    'cover_url' => $evento->primaryMedia()?->preview_url ?? $evento->primaryMedia()?->url,
                    'media' => $evento->multimedias->map(fn (Multimedia $media) => [
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