<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Inertia\Inertia;
use Inertia\Response;

class CotizacionController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Cotizacion::class);

        $query = Cotizacion::with('user');

        if ($request->user()->role === 'cliente') {
            $query->where('user_id', $request->user()->id);
        }

        $cotizaciones = $query->paginate(10);

        return Inertia::render('Cotizaciones/Index', [
            'cotizaciones' => $cotizaciones,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $this->authorize('create', Cotizacion::class);

        return Inertia::render('Cotizaciones/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Cotizacion::class);

        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'description' => 'required|string',
            'quantity' => 'required|integer|min:1',
        ]);

        $validated['user_id'] = $request->user()->id;

        Cotizacion::create($validated);

        return redirect()->route('cotizaciones.index')->with('success', 'Cotización created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Cotizacion $cotizacion): Response
    {
        $this->authorize('view', $cotizacion);

        return Inertia::render('Cotizaciones/Show', [
            'cotizacion' => $cotizacion->load('user'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cotizacion $cotizacion): Response
    {
        $this->authorize('update', $cotizacion);

        return Inertia::render('Cotizaciones/Edit', [
            'cotizacion' => $cotizacion,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cotizacion $cotizacion): RedirectResponse
    {
        $this->authorize('update', $cotizacion);

        $validated = $request->validate([
            'estimated_price' => 'nullable|numeric|min:0',
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $cotizacion->update($validated);

        return redirect()->route('cotizaciones.index')->with('success', 'Cotización updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cotizacion $cotizacion): RedirectResponse
    {
        $this->authorize('delete', $cotizacion);

        $cotizacion->delete();

        return redirect()->route('cotizaciones.index')->with('success', 'Cotización deleted successfully.');
    }
}
