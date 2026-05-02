<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Models\Tenant;

class WelcomeController extends Controller
{
    public function index()
    {
        $fotoAleph = Tenant::where('razon_social', 'Fotoaleph')->first();
        
        return Inertia::render('Welcome', [
            'socialNetworks' => $fotoAleph->aleatoriasRedesSociales(),
            'canRegister' => Features::enabled(Features::registration()),
        ]);
    }

    public function contacto()
    {
         $fotoAleph = Tenant::where('razon_social', 'Fotoaleph')->first();
        return Inertia::render('Contacto', [
            'socialNetworks' => $fotoAleph->aleatoriasRedesSociales(),
            'canRegister' => Features::enabled(Features::registration()),
        ]);
    }
}
