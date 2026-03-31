<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Vitrina;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ManagedTenantVitrinaController extends Controller
{
    use AuthorizesRequests;

    public function index(Tenant $tenant): JsonResponse
    {
        $this->authorize('create', [Vitrina::class, $tenant]);

        return response()->json(
            $this->transform(
                $tenant->vitrinas()->with(['tenant', 'categoria', 'grupo', 'nivel'])->latest()->get(),
            ),
        );
    }

    public function store(Request $request, Tenant $tenant): JsonResponse
    {
        $this->authorize('create', [Vitrina::class, $tenant]);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|string|max:255',
            'categoria' => 'nullable|string|max:255',
            'grupo' => 'nullable|string|max:255',
            'nivel' => 'nullable|integer|min:0',
        ]);

        $vitrina = $tenant->vitrinas()->create([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'imagen' => $validated['imagen'] ?? null,
        ]);

        $this->syncRelations($vitrina, $validated);

        return response()->json($this->transformOne($vitrina->fresh(['tenant', 'categoria', 'grupo', 'nivel'])), 201);
    }

    public function show(Tenant $tenant, Vitrina $vitrina): JsonResponse
    {
        $this->ensureTenantMatch($tenant, $vitrina);
        $this->authorize('view', $vitrina);

        return response()->json($this->transformOne($vitrina->load(['tenant', 'categoria', 'grupo', 'nivel'])));
    }

    public function update(Request $request, Tenant $tenant, Vitrina $vitrina): JsonResponse
    {
        $this->ensureTenantMatch($tenant, $vitrina);
        $this->authorize('update', $vitrina);

        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|string|max:255',
            'categoria' => 'nullable|string|max:255',
            'grupo' => 'nullable|string|max:255',
            'nivel' => 'nullable|integer|min:0',
        ]);

        $vitrina->update([
            'nombre' => $validated['nombre'] ?? $vitrina->nombre,
            'descripcion' => array_key_exists('descripcion', $validated) ? $validated['descripcion'] : $vitrina->descripcion,
            'imagen' => array_key_exists('imagen', $validated) ? $validated['imagen'] : $vitrina->imagen,
        ]);

        $this->syncRelations($vitrina, $validated);

        return response()->json($this->transformOne($vitrina->fresh(['tenant', 'categoria', 'grupo', 'nivel'])));
    }

    public function destroy(Tenant $tenant, Vitrina $vitrina): JsonResponse
    {
        $this->ensureTenantMatch($tenant, $vitrina);
        $this->authorize('delete', $vitrina);

        $vitrina->delete();

        return response()->json(['message' => 'Vitrina eliminada exitosamente.']);
    }

    private function ensureTenantMatch(Tenant $tenant, Vitrina $vitrina): void
    {
        abort_unless($vitrina->tenant_id === $tenant->id, 404);
    }

    private function syncRelations(Vitrina $vitrina, array $validated): void
    {
        if (array_key_exists('categoria', $validated) && $validated['categoria'] !== null) {
            $vitrina->categoria()->updateOrCreate([], [
                'nombre' => $validated['categoria'],
                'descripcion' => $validated['categoria'],
            ]);
        }

        if (array_key_exists('grupo', $validated) && $validated['grupo'] !== null) {
            $vitrina->grupo()->updateOrCreate([], [
                'nombre' => $validated['grupo'],
                'descripcion' => $validated['grupo'],
            ]);
        }

        if (array_key_exists('nivel', $validated) && $validated['nivel'] !== null) {
            $vitrina->nivel()->updateOrCreate([], [
                'nivel' => $validated['nivel'],
            ]);
        }
    }

    private function transform(Collection $vitrinas): Collection
    {
        return $vitrinas->map(fn (Vitrina $vitrina) => $this->transformOne($vitrina))->values();
    }

    private function transformOne(Vitrina $vitrina): array
    {
        return [
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
        ];
    }
}
