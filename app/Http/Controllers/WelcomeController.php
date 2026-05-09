<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Inertia\Inertia;
use Laravel\Fortify\Features;

class WelcomeController extends Controller
{
    public function index()
    {
        return Inertia::render('Welcome', $this->publicPageProps());
    }

    public function contacto()
    {
        return Inertia::render('Contacto', $this->publicPageProps());
    }

    public function proyectos()
    {
        return Inertia::render('Proyectos', $this->publicPageProps());
    }

    public function productos()
    {
        return Inertia::render('Productos', $this->publicPageProps());
    }

    private function publicPageProps(): array
    {
        $fotoAleph = Tenant::where('razon_social', 'Fotoaleph')->first();

        return [
            'socialNetworks' => $fotoAleph?->aleatoriasRedesSociales() ?? [],
            'canRegister' => Features::enabled(Features::registration()),
        ];
    }
}
