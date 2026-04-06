<?php

namespace App\Http\Controllers;

use App\Models\BiotekEstudiante;
use App\Models\Multimedia;
use App\Models\Taller;
use App\Models\Tenant;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BiotekEstudianteController extends Controller
{
    use AuthorizesRequests;

    public function index(Tenant $tenant): Response
    {
        return $this->renderPage($tenant);
    }

    public function create(Tenant $tenant): Response
    {
        return $this->renderPage($tenant, new BiotekEstudiante());
    }

    public function store(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->authorizeTenant($tenant);

        $validated = $this->validatePayload($request);

        $estudiante = BiotekEstudiante::query()->create([
            'nombres' => trim($validated['nombres']),
            'apellidos' => trim($validated['apellidos']),
            'identificacion' => trim($validated['identificacion']),
        ]);

        $this->syncFoto($estudiante, $validated['foto'] ?? null);
        $this->syncTalleres($estudiante, $validated['talleres'] ?? []);

        return redirect()->route('biotek-students.index', $tenant)->with('success', 'Estudiante de Biotek guardado exitosamente.');
    }

    public function edit(Tenant $tenant, BiotekEstudiante $biotekEstudiante): Response
    {
        $this->authorizeTenant($tenant);

        return $this->renderPage($tenant, $biotekEstudiante->load(['multimedias', 'talleres']));
    }

    public function update(Request $request, Tenant $tenant, BiotekEstudiante $biotekEstudiante): RedirectResponse
    {
        $this->authorizeTenant($tenant);

        $validated = $this->validatePayload($request, $biotekEstudiante);

        $biotekEstudiante->update([
            'nombres' => trim($validated['nombres']),
            'apellidos' => trim($validated['apellidos']),
            'identificacion' => trim($validated['identificacion']),
        ]);

        $this->syncFoto($biotekEstudiante, $validated['foto'] ?? null);
        $this->syncTalleres($biotekEstudiante, $validated['talleres'] ?? []);

        return redirect()->route('biotek-students.index', $tenant)->with('success', 'Estudiante de Biotek actualizado exitosamente.');
    }

    public function destroy(Tenant $tenant, BiotekEstudiante $biotekEstudiante): RedirectResponse
    {
        $this->authorizeTenant($tenant);

        $biotekEstudiante->delete();

        return redirect()->route('biotek-students.index', $tenant)->with('success', 'Estudiante de Biotek eliminado exitosamente.');
    }

    private function renderPage(Tenant $tenant, ?BiotekEstudiante $student = null): Response
    {
        $this->authorizeTenant($tenant);

        $student?->loadMissing(['multimedias', 'talleres']);

        return Inertia::render('BiotekStudents/Index', [
            'tenant' => [
                'id' => $tenant->id,
                'razon_social' => $tenant->razon_social,
            ],
            'students' => BiotekEstudiante::query()
                ->with(['multimedias', 'talleres'])
                ->latest()
                ->get()
                ->map(fn (BiotekEstudiante $item) => [
                    'id' => $item->id,
                    'nombres' => $item->nombres,
                    'apellidos' => $item->apellidos,
                    'nombre_completo' => trim($item->nombres.' '.$item->apellidos),
                    'identificacion' => $item->identificacion,
                    'foto_src' => $item->primaryMedia()?->preview_url ?? $item->primaryMedia()?->url,
                    'talleres' => $item->talleres->map(fn (Taller $taller) => [
                        'id' => $taller->id,
                        'nombre' => $taller->nombre,
                        'fecha' => $taller->fecha,
                        'duracion' => $taller->duracion,
                        'pago' => (float) $taller->pivot->pago,
                        'abono' => (float) $taller->pivot->abono,
                        'debe' => (float) $taller->pivot->debe,
                        'saldo_total' => (float) $taller->pivot->saldo_total,
                    ])->values(),
                ]),
            'form' => [
                'id' => $student?->id,
                'nombres' => $student?->nombres ?? '',
                'apellidos' => $student?->apellidos ?? '',
                'identificacion' => $student?->identificacion ?? '',
                'foto' => $student?->primaryMedia()?->url ?? '',
                'talleres' => $student
                    ? $student->talleres->map(fn (Taller $taller) => [
                        'nombre' => $taller->nombre,
                        'fecha' => $taller->fecha,
                        'duracion' => $taller->duracion,
                        'pago' => (float) $taller->pivot->pago,
                        'abono' => (float) $taller->pivot->abono,
                        'debe' => (float) $taller->pivot->debe,
                        'saldo_total' => (float) $taller->pivot->saldo_total,
                    ])->values()->all()
                    : [[
                        'nombre' => '',
                        'fecha' => '',
                        'duracion' => '',
                        'pago' => 0,
                        'abono' => 0,
                        'debe' => 0,
                        'saldo_total' => 0,
                    ]],
            ],
            'isEditing' => $student?->exists ?? false,
        ]);
    }

    private function authorizeTenant(Tenant $tenant): void
    {
        abort_unless($tenant->databaseConnectionName() === 'tenant_biotek', 404);
        Gate::authorize('manage-tenant', $tenant);
    }

    private function validatePayload(Request $request, ?BiotekEstudiante $student = null): array
    {
        return $request->validate([
            'nombres' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'identificacion' => ['required', 'string', 'max:255', Rule::unique('tenant_biotek.estudiantes', 'identificacion')->ignore($student?->id)],
            'foto' => ['nullable', 'string', 'max:255'],
            'talleres' => ['nullable', 'array'],
            'talleres.*.nombre' => ['nullable', 'string', 'max:255'],
            'talleres.*.fecha' => ['nullable', 'string', 'max:255'],
            'talleres.*.duracion' => ['nullable', 'string'],
            'talleres.*.pago' => ['nullable', 'numeric', 'min:0'],
            'talleres.*.abono' => ['nullable', 'numeric', 'min:0'],
            'talleres.*.debe' => ['nullable', 'numeric', 'min:0'],
            'talleres.*.saldo_total' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    private function syncFoto(BiotekEstudiante $student, ?string $path): void
    {
        $path = trim((string) $path);

        if ($path === '') {
            $student->multimedias()->sync([]);

            return;
        }

        $multimedia = Multimedia::on('tenant_biotek')->firstOrCreate(
            [
                'url' => $path,
                'preview_url' => $path,
                'type' => 'image',
            ],
            [
                'mime_type' => $this->guessMimeType($path),
            ],
        );

        $student->multimedias()->sync([$multimedia->id]);
    }

    private function syncTalleres(BiotekEstudiante $student, array $rows): void
    {
        $payload = collect($rows)
            ->filter(fn (array $row) => trim((string) ($row['nombre'] ?? '')) !== '')
            ->mapWithKeys(function (array $row) {
                $taller = Taller::query()->firstOrCreate(
                    [
                        'nombre' => trim((string) $row['nombre']),
                        'fecha' => trim((string) ($row['fecha'] ?? '')),
                    ],
                    [
                        'duracion' => trim((string) ($row['duracion'] ?? '')),
                    ],
                );

                $pago = (float) ($row['pago'] ?? 0);
                $abono = (float) ($row['abono'] ?? 0);
                $debe = isset($row['debe']) && $row['debe'] !== '' ? (float) $row['debe'] : max($pago - $abono, 0);
                $saldoTotal = isset($row['saldo_total']) && $row['saldo_total'] !== '' ? (float) $row['saldo_total'] : $debe;

                return [
                    $taller->id => [
                        'pago' => $pago,
                        'abono' => $abono,
                        'debe' => $debe,
                        'saldo_total' => $saldoTotal,
                    ],
                ];
            })
            ->all();

        $student->talleres()->sync($payload);
    }

    private function guessMimeType(string $url): string
    {
        return match (Str::lower(pathinfo($url, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };
    }
}