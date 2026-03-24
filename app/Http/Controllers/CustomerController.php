<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\Inertia;

class CustomerController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of customers.
     */
    public function index(Request $request): Response
    {
        // Solo administradores pueden ver el listado de clientes
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'coordinador') {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        $customers = User::where('role', 'cliente')->paginate(10);

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
        ]);
    }
}
