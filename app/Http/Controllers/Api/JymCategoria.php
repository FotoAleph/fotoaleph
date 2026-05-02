<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class JymCategoria extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        response()->json([
            'status' => 'success',
            'data' => [
                ['id' => 1, 'name' => 'Categoría 1'],
                ['id' => 2, 'name' => 'Categoría 2'],
                ['id' => 3, 'name' => 'Categoría 3'],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
