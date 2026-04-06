<?php

namespace App\Http\Controllers;

use App\Models\JymCategoria;
use App\Models\JymGrupo;
use App\Models\Multimedia;
use App\Models\Proyecto;
use App\Models\Tenant;
use App\Support\Tenants\TenantCatalogVitrinaSynchronizer;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TenantProyectoController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly TenantCatalogVitrinaSynchronizer $vitrinaSynchronizer,
    ) {}

    public function index(Tenant $tenant): Response
    {
        return $this->renderPage($tenant);
    }

    public function create(Tenant $tenant): Response
    {
        return $this->renderPage($tenant, new Proyecto());
    }

    public function store(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->authorizeTenant($tenant);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'categoria' => ['required', 'string', 'max:255'],
            'grupo' => ['required', 'string', 'max:255'],
            'publicar_en_vitrina' => ['nullable', 'boolean'],
            'media_urls' => ['nullable', 'string'],
        ]);

        $categoria = JymCategoria::query()->firstOrCreate(
            ['nombre' => trim($validated['categoria'])],
            ['descripcion' => trim($validated['categoria'])],
        );

        $grupo = JymGrupo::query()->firstOrCreate(
            ['nombre' => trim($validated['grupo'])],
            ['descripcion' => trim($validated['grupo'])],
        );

        $proyecto = Proyecto::query()->create([
            'nombre' => trim($validated['nombre']),
            'descripcion' => $validated['descripcion'] ?? null,
            'categoria_id' => $categoria->id,
            'grupo_id' => $grupo->id,
            'publicar_en_vitrina' => (bool) ($validated['publicar_en_vitrina'] ?? false),
        ]);

        $this->syncMedia($proyecto, $validated['media_urls'] ?? null);

        $proyecto->load(['categoria', 'grupo', 'multimedias']);

        $this->vitrinaSynchronizer->sync(
            $tenant,
            $proyecto,
            $proyecto->nombre,
            $proyecto->descripcion,
            $proyecto->categoria?->nombre,
            $proyecto->grupo?->nombre,
            $proyecto->multimedias,
            $proyecto->publicar_en_vitrina,
        );

        return redirect()->route('tenant-projects.index', $tenant)->with('success', 'Proyecto guardado exitosamente.');
    }

    public function edit(Tenant $tenant, Proyecto $proyecto): Response
    {
        $this->authorizeTenant($tenant);

        return $this->renderPage($tenant, $proyecto->load(['categoria', 'grupo', 'multimedias']));
    }

    public function update(Request $request, Tenant $tenant, Proyecto $proyecto): RedirectResponse
    {
        $this->authorizeTenant($tenant);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255', Rule::unique('tenant_jym.proyectos', 'nombre')->ignore($proyecto->id)],
            'descripcion' => ['nullable', 'string'],
            'categoria' => ['required', 'string', 'max:255'],
            'grupo' => ['required', 'string', 'max:255'],
            'publicar_en_vitrina' => ['nullable', 'boolean'],
            'media_urls' => ['nullable', 'string'],
        ]);

        $categoria = JymCategoria::query()->firstOrCreate(
            ['nombre' => trim($validated['categoria'])],
            ['descripcion' => trim($validated['categoria'])],
        );

        $grupo = JymGrupo::query()->firstOrCreate(
            ['nombre' => trim($validated['grupo'])],
            ['descripcion' => trim($validated['grupo'])],
        );

        $proyecto->update([
            'nombre' => trim($validated['nombre']),
            'descripcion' => $validated['descripcion'] ?? null,
            'categoria_id' => $categoria->id,
            'grupo_id' => $grupo->id,
            'publicar_en_vitrina' => (bool) ($validated['publicar_en_vitrina'] ?? false),
        ]);

        $this->syncMedia($proyecto, $validated['media_urls'] ?? null);

        $proyecto->load(['categoria', 'grupo', 'multimedias']);

        $this->vitrinaSynchronizer->sync(
            $tenant,
            $proyecto,
            $proyecto->nombre,
            $proyecto->descripcion,
            $proyecto->categoria?->nombre,
            $proyecto->grupo?->nombre,
            $proyecto->multimedias,
            $proyecto->publicar_en_vitrina,
        );

        return redirect()->route('tenant-projects.index', $tenant)->with('success', 'Proyecto actualizado exitosamente.');
    }

    public function destroy(Tenant $tenant, Proyecto $proyecto): RedirectResponse
    {
        $this->authorizeTenant($tenant);

        $proyecto->load(['categoria', 'grupo', 'multimedias']);

        $this->vitrinaSynchronizer->sync(
            $tenant,
            $proyecto,
            $proyecto->nombre,
            $proyecto->descripcion,
            $proyecto->categoria?->nombre,
            $proyecto->grupo?->nombre,
            $proyecto->multimedias,
            false,
        );

        $proyecto->delete();

        return redirect()->route('tenant-projects.index', $tenant)->with('success', 'Proyecto eliminado exitosamente.');
    }

    private function renderPage(Tenant $tenant, ?Proyecto $proyecto = null): Response
    {
        $this->authorizeTenant($tenant);

        $proyecto?->loadMissing(['categoria', 'grupo', 'multimedias']);

        return Inertia::render('TenantProjects/Index', [
            'tenant' => [
                'id' => $tenant->id,
                'razon_social' => $tenant->razon_social,
            ],
            'projects' => Proyecto::query()
                ->with(['categoria', 'grupo', 'multimedias'])
                ->latest()
                ->get()
                ->map(fn (Proyecto $item) => [
                    'id' => $item->id,
                    'nombre' => $item->nombre,
                    'descripcion' => $item->descripcion,
                    'categoria' => $item->categoria?->nombre,
                    'grupo' => $item->grupo?->nombre,
                    'publicar_en_vitrina' => (bool) $item->publicar_en_vitrina,
                    'cover_url' => $item->primaryMedia()?->preview_url ?? $item->primaryMedia()?->url,
                    'media_urls' => $item->multimedias->map(fn (Multimedia $media) => [
                        'id' => $media->id,
                        'url' => $media->url,
                        'preview_url' => $media->preview_url,
                        'type' => $media->type,
                    ])->values(),
                ]),
            'form' => [
                'id' => $proyecto?->id,
                'nombre' => $proyecto?->nombre ?? '',
                'descripcion' => $proyecto?->descripcion ?? '',
                'categoria' => $proyecto?->categoria?->nombre ?? '',
                'grupo' => $proyecto?->grupo?->nombre ?? '',
                'publicar_en_vitrina' => (bool) ($proyecto?->publicar_en_vitrina ?? false),
                'media_urls' => $proyecto ? $proyecto->multimedias->pluck('url')->implode("\n") : '',
            ],
            'isEditing' => $proyecto?->exists ?? false,
        ]);
    }

    private function authorizeTenant(Tenant $tenant): void
    {
        abort_unless($tenant->databaseConnectionName() === 'tenant_jym', 404);
        Gate::authorize('manage-tenant', $tenant);
    }

    private function syncMedia(Proyecto $proyecto, ?string $rawUrls): void
    {
        $payload = collect(preg_split('/\r\n|\r|\n/', trim((string) $rawUrls)))
            ->map(fn (?string $url) => trim((string) $url))
            ->filter()
            ->values()
            ->mapWithKeys(function (string $url) {
                $media = Multimedia::on('tenant_jym')->firstOrCreate(
                    [
                        'url' => $url,
                        'preview_url' => $url,
                        'type' => $this->guessMediaType($url),
                    ],
                    [
                        'mime_type' => $this->guessMimeType($url),
                    ],
                );

                return [$media->id => []];
            })
            ->all();

        $proyecto->multimedias()->sync($payload);
    }

    private function guessMediaType(string $url): string
    {
        return in_array(Str::lower(pathinfo($url, PATHINFO_EXTENSION)), ['mp4', 'webm', 'ogg'], true)
            ? 'video'
            : 'image';
    }

    private function guessMimeType(string $url): string
    {
        return match (Str::lower(pathinfo($url, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'ogg' => 'video/ogg',
            default => 'image/jpeg',
        };
    }
}