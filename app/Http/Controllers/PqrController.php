<?php

namespace App\Http\Controllers;

use App\Models\Pqr;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Inertia\Inertia;
use Inertia\Response;

class PqrController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Pqr::class);

        $query = Pqr::with('user');

        if ($request->user()->role === 'cliente') {
            $query->where('user_id', $request->user()->id);
        }

        $pqrs = $query->paginate(10);

        return Inertia::render('Pqrs/Index', [
            'pqrs' => $pqrs,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $this->authorize('create', Pqr::class);

        return Inertia::render('Pqrs/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Pqr::class);

        $validated = $request->validate([
            'type' => 'required|in:P,Q,R',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $validated['user_id'] = $request->user()->id;

        Pqr::create($validated);

        return redirect()->route('pqrs.index')->with('success', 'PQR created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Pqr $pqr): Response
    {
        $this->authorize('view', $pqr);

        return Inertia::render('Pqrs/Show', [
            'pqr' => $pqr->load('user'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pqr $pqr): Response
    {
        $this->authorize('update', $pqr);

        return Inertia::render('Pqrs/Edit', [
            'pqr' => $pqr,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pqr $pqr): RedirectResponse
    {
        $this->authorize('update', $pqr);

        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,closed',
        ]);

        $pqr->update($validated);

        return redirect()->route('pqrs.index')->with('success', 'PQR updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pqr $pqr): RedirectResponse
    {
        $this->authorize('delete', $pqr);

        $pqr->delete();

        return redirect()->route('pqrs.index')->with('success', 'PQR deleted successfully.');
    }
}
