<?php

namespace App\Http\Controllers;

use App\Models\SocialNetwork;
use App\Models\Tenant;
use Inertia\Inertia;
use Laravel\Fortify\Features;

class WelcomeController extends Controller
{
    public function index()
    {
        $fotoAleph = Tenant::where('razon_social', 'Fotoaleph')->first();

        return Inertia::render('Welcome', [
            'socialNetworks' => $fotoAleph
                ? $fotoAleph->aleatoriasRedesSociales()
                    ->with('socialNetworkType')
                    ->get()
                    ->map(fn (SocialNetwork $network): array => $this->formatSocialNetwork($network))
                    ->values()
                : [],
            'canRegister' => Features::enabled(Features::registration()),
        ]);
    }

    private function formatSocialNetwork(SocialNetwork $network): array
    {
        return [
            'name' => $network->socialNetworkType?->name ?? '',
            'url' => $network->url,
            'icon' => $network->socialNetworkType?->icon ?? '',
        ];
    }
}
