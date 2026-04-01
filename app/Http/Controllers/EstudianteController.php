<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\Multimedia;
use App\Pipelines\Estudiantes\FiltrarPorCategoria;
use App\Pipelines\Estudiantes\FiltrarPorNombre;
use App\Pipelines\Estudiantes\OrdenarPorCampo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class EstudianteController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Estudiante::class);

        $filters = $this->validatedFilters($request);
        $query = $this->buildFilteredQuery($filters);

        $estudiantes = $query->paginate((int) ($filters['per_page'] ?? 10))->withQueryString();

        $mediaIds = $estudiantes->getCollection()
            ->pluck('foto_url')
            ->filter()
            ->unique()
            ->values();

        $multimedia = Multimedia::on('tenant_sport_bogota')
            ->whereIn('id', $mediaIds)
            ->get(['id', 'url', 'preview_url'])
            ->keyBy('id');

        $estudiantes->setCollection(
            $estudiantes->getCollection()->map(function (Estudiante $estudiante) use ($multimedia) {
                $foto = $estudiante->foto_url ? $multimedia->get((int) $estudiante->foto_url) : null;

                $estudiante->setAttribute('foto_src', $foto?->preview_url ?? $foto?->url);

                return $estudiante;
            }),
        );

        return Inertia::render('Estudiantes/Index', [
            'estudiantes' => $estudiantes,
            'filters' => [
                'nombre' => $filters['nombre'] ?? '',
                'categoria' => $filters['categoria'] ?? '',
                'sort_by' => $filters['sort_by'] ?? 'created_at',
                'sort_dir' => $filters['sort_dir'] ?? 'desc',
                'per_page' => (int) ($filters['per_page'] ?? 10),
            ],
        ]);
    }

    public function download(Estudiante $estudiante): BinaryFileResponse
    {
        $this->authorize('view', $estudiante);

        $foto = $this->resolveFotoById($estudiante->foto_url);

        abort_if($foto === null, 404, 'El estudiante no tiene imagen registrada.');

        $absolutePath = $this->resolveAbsolutePath((string) ($foto->preview_url ?? $foto->url));

        abort_unless($absolutePath !== null && is_file($absolutePath), 404, 'No se encontro el archivo en storage/public.');

        $extension = pathinfo($absolutePath, PATHINFO_EXTENSION) ?: 'jpg';
        $filename = Str::slug((string) $estudiante->nombre).'-'.$estudiante->id.'.'.$extension;

        return response()->download($absolutePath, $filename);
    }

    public function downloadAll(Request $request): BinaryFileResponse
    {
        $this->authorize('viewAny', Estudiante::class);

        $filters = $this->validatedFilters($request);
        $query = $this->buildFilteredQuery($filters);

        $estudiantes = $query->get(['id', 'nombre', 'foto_url']);

        $zipPath = tempnam(sys_get_temp_dir(), 'sport_bogota_estudiantes_');

        if ($zipPath === false) {
            abort(500, 'No fue posible preparar el archivo ZIP.');
        }

        $zip = new ZipArchive();
        $opened = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($opened !== true) {
            @unlink($zipPath);
            abort(500, 'No fue posible crear el archivo ZIP.');
        }

        $added = 0;

        foreach ($estudiantes as $estudiante) {
            $foto = $this->resolveFotoById($estudiante->foto_url);

            if ($foto === null) {
                continue;
            }

            $absolutePath = $this->resolveAbsolutePath((string) ($foto->preview_url ?? $foto->url));

            if ($absolutePath === null || ! is_file($absolutePath)) {
                continue;
            }

            $extension = pathinfo($absolutePath, PATHINFO_EXTENSION) ?: 'jpg';
            $entryName = Str::slug((string) $estudiante->nombre).'-'.$estudiante->id.'.'.$extension;

            if ($zip->addFile($absolutePath, $entryName)) {
                $added++;
            }
        }

        $zip->close();

        if ($added === 0) {
            @unlink($zipPath);
            abort(404, 'No hay imagenes disponibles para descargar.');
        }

        $timestamp = now()->format('Ymd_His');

        return response()
            ->download($zipPath, "sport_bogota_estudiantes_{$timestamp}.zip")
            ->deleteFileAfterSend(true);
    }

    public function create(): Response
    {
        $this->authorize('create', Estudiante::class);

        return Inertia::render('Estudiantes/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Estudiante::class);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria' => 'required|string|max:255',
            'foto' => 'nullable|string|max:255',
        ]);

        $multimediaId = null;

        if (! empty($validated['foto'])) {
            $multimedia = Multimedia::on('tenant_sport_bogota')->firstOrCreate(
                [
                    'url' => $validated['foto'],
                    'preview_url' => $validated['foto'],
                    'type' => 'image',
                ],
                [
                    'mime_type' => $this->guessMimeType($validated['foto']),
                ],
            );

            $multimediaId = $multimedia->id;
        }

        Estudiante::query()->create([
            'nombre' => $validated['nombre'],
            'categoria' => $validated['categoria'],
            'foto_url' => $multimediaId,
        ]);

        return redirect()->route('estudiantes.index')->with('success', 'Estudiante creado exitosamente.');
    }

    public function edit(Estudiante $estudiante): Response
    {
        $this->authorize('update', $estudiante);

        $foto = null;

        if ($estudiante->foto_url) {
            $foto = Multimedia::on('tenant_sport_bogota')
                ->whereKey($estudiante->foto_url)
                ->first(['id', 'url', 'preview_url']);
        }

        $estudiante->setAttribute('foto_src', $foto?->preview_url ?? $foto?->url);

        return Inertia::render('Estudiantes/Edit', [
            'estudiante' => $estudiante,
        ]);
    }

    public function update(Request $request, Estudiante $estudiante): RedirectResponse
    {
        $this->authorize('update', $estudiante);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria' => 'required|string|max:255',
            'foto' => 'nullable|string|max:255',
        ]);

        $multimediaId = null;

        if (! empty($validated['foto'])) {
            $multimedia = Multimedia::on('tenant_sport_bogota')->firstOrCreate(
                [
                    'url' => $validated['foto'],
                    'preview_url' => $validated['foto'],
                    'type' => 'image',
                ],
                [
                    'mime_type' => $this->guessMimeType($validated['foto']),
                ],
            );

            $multimediaId = $multimedia->id;
        }

        $estudiante->update([
            'nombre' => $validated['nombre'],
            'categoria' => $validated['categoria'],
            'foto_url' => $multimediaId,
        ]);

        return redirect()->route('estudiantes.index')->with('success', 'Estudiante actualizado exitosamente.');
    }

    public function destroy(Estudiante $estudiante): RedirectResponse
    {
        $this->authorize('delete', $estudiante);

        $estudiante->delete();

        return redirect()->route('estudiantes.index')->with('success', 'Estudiante eliminado exitosamente.');
    }

    private function guessMimeType(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'nombre' => 'nullable|string|max:255',
            'categoria' => 'nullable|string|max:255',
            'sort_by' => 'nullable|in:nombre,categoria,created_at',
            'sort_dir' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|in:5,10,15,25,50',
        ]);
    }

    private function buildFilteredQuery(array $filters)
    {
        return app(Pipeline::class)
            ->send(Estudiante::query())
            ->through([
                new FiltrarPorNombre($filters['nombre'] ?? null),
                new FiltrarPorCategoria($filters['categoria'] ?? null),
                new OrdenarPorCampo(
                    $filters['sort_by'] ?? 'created_at',
                    $filters['sort_dir'] ?? 'desc',
                ),
            ])
            ->thenReturn();
    }

    private function resolveFotoById(?int $fotoId): ?Multimedia
    {
        if (! $fotoId) {
            return null;
        }

        return Multimedia::on('tenant_sport_bogota')
            ->whereKey($fotoId)
            ->first(['id', 'url', 'preview_url']);
    }

    private function resolveAbsolutePath(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: $url;
        $path = trim($path);

        if ($path === '') {
            return null;
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            return storage_path('app/public/'.Str::after($path, 'storage/'));
        }

        if (str_starts_with($path, 'IMG/')) {
            return public_path($path);
        }

        $storageCandidate = storage_path('app/public/'.$path);

        if (is_file($storageCandidate)) {
            return $storageCandidate;
        }

        $publicCandidate = public_path($path);

        if (is_file($publicCandidate)) {
            return $publicCandidate;
        }

        return $storageCandidate;
    }
}
