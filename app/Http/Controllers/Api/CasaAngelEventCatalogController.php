<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\Multimedia;
use App\Support\Api\IntegerCounterMutation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CasaAngelEventCatalogController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Evento::class);

        $query = Evento::query();

        if ($request->user()->isCliente()) {
            $query->where('user_id', $request->user()->id);
        }

        return response()->json(
            $query->paginate(10)->through(fn (Evento $evento) => [
                'id' => $evento->id,
                'name' => $evento->nombre,
                'description' => $evento->descripcion,
                'entregado' => $evento->entregado,
                'date' => $evento->fecha_evento?->toISOString(),
            ]),
        );
    }

    public function show(Evento $evento): JsonResponse
    {
        $this->authorize('view', $evento);
        $evento->load(['multimedias']);

        return response()->json([
            'id' => $evento->id,
            'name' => $evento->nombre,
            'description' => $evento->descripcion,
            'date' => $evento->fecha_evento?->toISOString(),
            'multimedia' => $evento->multimedias,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Evento::class);

        $evento = Evento::query()->create($this->validatedData($request));

        return response()->json($evento, 201);
    }

    public function update(Request $request, Evento $evento): JsonResponse
    {
        $this->authorize('update', $evento);

        $evento->update($this->validatedData($request, true));

        return response()->json($evento->fresh());
    }

    public function destroy(Evento $evento): JsonResponse
    {
        $this->authorize('delete', $evento);

        $evento->delete();

        return response()->json(['message' => 'Evento eliminado exitosamente.']);
    }

    public function updateCantidad(Request $request, Evento $evento, int $multimedia, IntegerCounterMutation $mutation): JsonResponse
    {
        $this->authorize('update', $evento);

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

    private function validatedData(Request $request, bool $partial = false): array
    {
        $rules = [
            'nombre' => [$partial ? 'sometimes' : 'required', 'string', 'max:255'],
            'descripcion' => ['sometimes', 'nullable', 'string'],
            'fecha_evento' => ['sometimes', 'nullable', 'date'],
            'entregado' => ['sometimes', 'nullable', 'date'],
            'ubicacion' => ['sometimes', 'nullable', 'string', 'max:255'],
            'codigo' => ['sometimes', 'nullable', 'string', 'max:255'],
            'user_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
        ];

        return $request->validate($rules);
    }

    private function transformMedia(Evento $evento, Multimedia $media): array
    {
        $filename = basename((string) $media->url);

        return [
            'id' => $media->id,
            'img' => $media->preview_url ?: $media->url,
            'img_detail' => $media->url,
            'media_type' => $media->type,
            'aspect_ratio' => $media->aspect_ratio,
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