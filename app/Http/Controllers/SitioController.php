<?php

namespace App\Http\Controllers;

use App\Models\Sitio;
use App\Models\Tenant;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SitioController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $this->authorize('viewAny', Sitio::class);

        $sitios = Sitio::with('tenant')->paginate(10);

        return Inertia::render('Sitios/Index', [
            'sitios' => $sitios,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $this->authorize('create', Sitio::class);

        return Inertia::render('Sitios/Create', [
            'tenants' => Tenant::select('id', 'razon_social')->orderBy('razon_social')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Sitio::class);

        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'url' => 'nullable|url|max:255',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'estado' => 'required|in:activo,inactivo',
        ]);

        Sitio::create($validated);

        return redirect()->route('sitios.index')->with('success', 'Sitio creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Sitio $sitio): Response
    {
        $this->authorize('view', $sitio);

        return Inertia::render('Sitios/Show', [
            'sitio' => $sitio->load('tenant'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sitio $sitio): Response
    {
        $this->authorize('update', $sitio);

        return Inertia::render('Sitios/Edit', [
            'sitio' => $sitio,
            'tenants' => Tenant::select('id', 'razon_social')->orderBy('razon_social')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sitio $sitio): RedirectResponse
    {
        $this->authorize('update', $sitio);

        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'url' => 'nullable|url|max:255',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'estado' => 'required|in:activo,inactivo',
        ]);

        $sitio->update($validated);

        return redirect()->route('sitios.index')->with('success', 'Sitio actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sitio $sitio): RedirectResponse
    {
        $this->authorize('delete', $sitio);

        $sitio->delete();

        return redirect()->route('sitios.index')->with('success', 'Sitio eliminado exitosamente.');
    }
}
