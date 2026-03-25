<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class GrupoController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $this->authorize('viewAny', Grupo::class);

        $grupos = Grupo::orderBy('nombre')->paginate(10);

        return Inertia::render('Grupos/Index', [
            'grupos' => $grupos,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $this->authorize('create', Grupo::class);

        return Inertia::render('Grupos/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Grupo::class);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:255',
            'nivel' => 'required|integer|min:0',
        ]);

        Grupo::create($validated);

        return redirect()->route('grupos.index')->with('success', 'Grupo creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Grupo $grupo): Response
    {
        $this->authorize('view', $grupo);

        return Inertia::render('Grupos/Show', [
            'grupo' => $grupo,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Grupo $grupo): Response
    {
        $this->authorize('update', $grupo);

        return Inertia::render('Grupos/Edit', [
            'grupo' => $grupo,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Grupo $grupo): RedirectResponse
    {
        $this->authorize('update', $grupo);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:255',
            'nivel' => 'required|integer|min:0',
        ]);

        $grupo->update($validated);

        return redirect()->route('grupos.index')->with('success', 'Grupo actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Grupo $grupo): RedirectResponse
    {
        $this->authorize('delete', $grupo);

        $grupo->delete();

        return redirect()->route('grupos.index')->with('success', 'Grupo eliminado exitosamente.');
    }
}
