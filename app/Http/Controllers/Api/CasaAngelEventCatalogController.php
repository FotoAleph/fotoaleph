<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\Multimedia;
use App\Support\Api\IntegerCounterMutation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CasaAngelEventCatalogController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Evento::paginate(10)->through(fn (Evento $evento) => [
                'id' => $evento->id,
                'name' => $evento->nombre,
                'description' => $evento->descripcion,
                'date' => $evento->fecha_evento?->toISOString(),
            ]),
        );
    }

    public function show(Evento $evento): JsonResponse
    {
        $evento->load(['multimedias']);

        return response()->json([
            'id' => $evento->id,
            'name' => $evento->nombre,
            'description' => $evento->descripcion,
            'date' => $evento->fecha_evento?->toISOString(),
            'multimedia' => $evento->multimedias,
        ]);
    }
    public function updateCantidad(Request $request, Evento $evento, int $multimedia, IntegerCounterMutation $mutation): JsonResponse
    {
        $validated = $request->validate([
            'operation' => ['required', 'in:increment,decrement,set'],
            'value' => ['required', 'integer', 'min:0'],
        ]);

        $media = $evento->multimedias()->where('multimedia.id', $multimedia)->first();

        abort_unless($media instanceof Multimedia, 404);

        $cantidad = $mutation->apply($media, 'cantidad', $validated['operation'], (int) $validated['value']);

        return response()->json([
            'id' => $media->id,
            'cantidad' => $cantidad,
        ]);
    }

    private function transformMedia(Evento $evento, Multimedia $media): array
    {
        $filename = basename((string) $media->url);

        return [
            'id' => $media->id,
            'img' => $media->preview_url ?: $media->url,
            'img_detail' => $media->url,
            'media_type' => $media->type,
            'horientacion' => $media->orientacion,
            'orientacion' => $media->orientacion,
            'name' => $filename,
            'alt' => $media->alt ?: sprintf(
                'Evento %s del %s foto %s',
                $evento->nombre,
                substr((string) ($evento->fecha_evento?->toDateString() ?? $media->created_at?->toDateString() ?? now()->toDateString()), 0, 10),
                $filename,
            ),
            'date' => $media->created_at?->toISOString(),
            'cantidad' => $media->cantidad,
        ];
    }
}