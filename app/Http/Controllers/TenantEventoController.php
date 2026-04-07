<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Multimedia;
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

class TenantEventoController extends Controller
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
        return $this->renderPage($tenant, new Evento());
    }

    public function store(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->authorizeTenant($tenant);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'fecha_evento' => ['nullable', 'date'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'codigo' => ['nullable', 'string', 'max:255'],
            'publicar_en_vitrina' => ['nullable', 'boolean'],
            'media_urls' => ['nullable', 'string'],
        ]);

        $evento = Evento::query()->create([
            'nombre' => trim($validated['nombre']),
            'descripcion' => $validated['descripcion'] ?? null,
            'fecha_evento' => $validated['fecha_evento'] ?? null,
            'ubicacion' => $validated['ubicacion'] ?? null,
            'codigo' => $validated['codigo'] ?? null,
            'publicar_en_vitrina' => (bool) ($validated['publicar_en_vitrina'] ?? false),
        ]);

        $this->syncMedia($evento, $validated['media_urls'] ?? null);

        $evento->load(['multimedias', 'ocasion', 'tematica']);

        $this->vitrinaSynchronizer->sync(
            $tenant,
            $evento,
            $evento->nombre,
            $evento->descripcion,
            $evento->ocasion?->nombre,
            $evento->tematica?->nombre,
            $evento->multimedias,
            $evento->publicar_en_vitrina,
        );

        return redirect()->route('tenant-events.index', $tenant)->with('success', 'Evento guardado exitosamente.');
    }

    public function edit(Tenant $tenant, Evento $evento): Response
    {
        $this->authorizeTenant($tenant);

        return $this->renderPage($tenant, $evento->load('multimedias'));
    }

    public function update(Request $request, Tenant $tenant, Evento $evento): RedirectResponse
    {
        $this->authorizeTenant($tenant);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255', Rule::unique('tenant_casa_angel.eventos', 'nombre')->ignore($evento->id)],
            'descripcion' => ['nullable', 'string'],
            'fecha_evento' => ['nullable', 'date'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'codigo' => ['nullable', 'string', 'max:255'],
            'publicar_en_vitrina' => ['nullable', 'boolean'],
            'media_urls' => ['nullable', 'string'],
        ]);

        $evento->update([
            'nombre' => trim($validated['nombre']),
            'descripcion' => $validated['descripcion'] ?? null,
            'fecha_evento' => $validated['fecha_evento'] ?? null,
            'ubicacion' => $validated['ubicacion'] ?? null,
            'codigo' => $validated['codigo'] ?? null,
            'publicar_en_vitrina' => (bool) ($validated['publicar_en_vitrina'] ?? false),
        ]);

        $this->syncMedia($evento, $validated['media_urls'] ?? null);

        $evento->load(['multimedias', 'ocasion', 'tematica']);

        $this->vitrinaSynchronizer->sync(
            $tenant,
            $evento,
            $evento->nombre,
            $evento->descripcion,
            $evento->ocasion?->nombre,
            $evento->tematica?->nombre,
            $evento->multimedias,
            $evento->publicar_en_vitrina,
        );

        return redirect()->route('tenant-events.index', $tenant)->with('success', 'Evento actualizado exitosamente.');
    }

    public function destroy(Tenant $tenant, Evento $evento): RedirectResponse
    {
        $this->authorizeTenant($tenant);

        $evento->load(['multimedias', 'ocasion', 'tematica']);

        $this->vitrinaSynchronizer->sync(
            $tenant,
            $evento,
            $evento->nombre,
            $evento->descripcion,
            $evento->ocasion?->nombre,
            $evento->tematica?->nombre,
            $evento->multimedias,
            false,
        );

        $evento->delete();

        return redirect()->route('tenant-events.index', $tenant)->with('success', 'Evento eliminado exitosamente.');
    }

    private function renderPage(Tenant $tenant, ?Evento $evento = null): Response
    {
        $this->authorizeTenant($tenant);

        $evento?->loadMissing('multimedias');

        return Inertia::render('TenantEvents/Index', [
            'tenant' => [
                'id' => $tenant->id,
                'razon_social' => $tenant->razon_social,
            ],
            'events' => Evento::query()
                ->with('multimedias')
                ->latest('fecha_evento')
                ->get()
                ->map(fn (Evento $item) => [
                    'id' => $item->id,
                    'nombre' => $item->nombre,
                    'descripcion' => $item->descripcion,
                    'fecha_evento' => $item->fecha_evento?->toDateTimeString(),
                    'ubicacion' => $item->ubicacion,
                    'codigo' => $item->codigo,
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
                'id' => $evento?->id,
                'nombre' => $evento?->nombre ?? '',
                'descripcion' => $evento?->descripcion ?? '',
                'fecha_evento' => $evento?->fecha_evento?->format('Y-m-d\TH:i') ?? '',
                'ubicacion' => $evento?->ubicacion ?? '',
                'codigo' => $evento?->codigo ?? '',
                'publicar_en_vitrina' => (bool) ($evento?->publicar_en_vitrina ?? false),
                'media_urls' => $evento ? $evento->multimedias->pluck('url')->implode("\n") : '',
            ],
            'isEditing' => $evento?->exists ?? false,
        ]);
    }

    private function authorizeTenant(Tenant $tenant): void
    {
        abort_unless($tenant->databaseConnectionName() === 'tenant_casa_angel', 404);
        Gate::authorize('manage-tenant', $tenant);
    }

    private function syncMedia(Evento $evento, ?string $rawUrls): void
    {
        $payload = collect(preg_split('/\r\n|\r|\n/', trim((string) $rawUrls)))
            ->map(fn (?string $url) => trim((string) $url))
            ->filter()
            ->values()
            ->mapWithKeys(function (string $url) {
                $media = Multimedia::on('tenant_casa_angel')->firstOrCreate(
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

        $evento->multimedias()->sync($payload);
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