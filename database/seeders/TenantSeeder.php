<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SocialNetworkType;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dinamycode = Tenant::firstOrCreate(
            ['razon_social' => 'Dinamycode'],
            ['database_connection' => 'tenant_central'],
        );
        $dinamycode->forceFill(['database_connection' => 'tenant_central'])->save();
        $dinamycode->users()->syncWithoutDetaching([2]);

        $fotoAleph = Tenant::firstOrCreate(
            ['razon_social' => 'Fotoaleph'],
            ['database_connection' => 'tenant_central'],
        );
        $fotoAleph->forceFill(['database_connection' => 'tenant_central'])->save();
        $fotoAleph->users()->syncWithoutDetaching([3]);

        $fotoAleph->direcciones()->firstOrCreate([
            'nomenclatura' => 'Diagonal 69C sur # 78C - 36',
            'codigo_postal' => '110222',
        ]);

        $fotoAleph->telefonos()->firstOrCreate([
            'number' => '3014819820',
            'type' => 'movil',
        ]);

        foreach ($this->getSocialNetworks() as $network) {
            $type = SocialNetworkType::where('name', $network['name'])->first();

            if ($type) {
                $fotoAleph->redesSociales()->updateOrCreate(
                    [
                        'social_network_type_id' => $type->id,
                    ],
                    [
                        'url' => $network['url'],
                    ],
                );
            }
        }

        $biotek = Tenant::firstOrCreate(
            ['razon_social' => 'Biotek'],
            ['database_connection' => 'tenant_biotek'],
        );
        $biotek->forceFill(['database_connection' => 'tenant_biotek'])->save();
        $biotek->users()->syncWithoutDetaching([1]);

        $this->syncSitio(
            $biotek,
            'Biotek',
            'Talleres y carnetizacion Biotek',
            'biotek.com',
        );

        $casaAngel = Tenant::firstOrCreate(
            ['razon_social' => 'Casa Angel'],
            ['database_connection' => 'tenant_casa_angel'],
        );
        $casaAngel->forceFill(['database_connection' => 'tenant_casa_angel'])->save();
        $this->syncSitio(
            $casaAngel,
            'Casa Angel',
            'Sitio principal Casa Angel',
            'casaangel.com',
        );

        $vidriosJym = Tenant::firstOrCreate(
            ['razon_social' => 'Vidrios y Estructuras JyM'],
            ['database_connection' => 'tenant_jym'],
        );
        $vidriosJym->forceFill(['database_connection' => 'tenant_jym'])->save();
        $this->syncSitio(
            $vidriosJym,
            'Vidrios y Estructuras JyM',
            'Portafolio principal de Vidrios y Estructuras JyM',
            'vidriosyestructurasjym.com',
        );

        $sportBogota = Tenant::firstOrCreate(
            ['razon_social' => 'Sport Bogota'],
            ['database_connection' => 'tenant_sport_bogota'],
        );
        $sportBogota->forceFill(['database_connection' => 'tenant_sport_bogota'])->save();
        $sportBogota->users()->syncWithoutDetaching([4]);

        $sportBogota->direcciones()->firstOrCreate([
            'nomenclatura' => 'Calle 123 #45-67',
            'codigo_postal' => '110111',
        ]);

        $this->syncSitio(
            $sportBogota,
            'Sport Bogota',
            'Carnetizacion Sport Bogota',
            'sportbogota.com',
        );
    }

    private function syncSitio(Tenant $tenant, string $name, string $description, string $url): void
    {
        $tenant->sitios()->updateOrCreate(
            ['url' => $url],
            [
                'name' => $name,
                'description' => $description,
                'estado' => 'activo',
            ],
        );
    }

    private function getSocialNetworks(): array
    {
        return [
            [
                'name' => 'TikTok',
                'url' => 'https://www.tiktok.com/@carlos.alberto.ra295',
            ],
            [
                'name' => 'Facebook',
                'url' => 'https://www.facebook.com/FotoAleph',
            ],
            [
                'name' => 'Google Maps',
                'url' => 'https://maps.app.goo.gl/iWiMn7bWFLi24Rxy5',
            ],
            [
                'name' => 'LinkedIn',
                'url' => 'https://www.linkedin.com/in/carlos-alberto-ramirez/',
            ],
            [
                'name' => 'GitHub',
                'url' => 'https://github.com/FotoAleph',
            ],
            [
                'name' => 'YouTube',
                'url' => 'https://www.youtube.com/@FotoAleph',
            ],
            [
                'name' => 'Instagram',
                'url' => 'https://www.instagram.com/rlcirilo/',
            ],
            [
                'name' => 'Discord',
                'url' => 'https://discord.com/fotoaleph',
            ],
            [
                'name' => 'X / Twitter',
                'url' => 'https://x.com/rlcirilo',
            ],
            [
                'name' => 'Reddit',
                'url' => 'https://www.reddit.com/user/Known-Gate-2912/',
            ],
            [
                'name' => 'Figma',
                'url' => 'https://www.figma.com/@carlosramirez9',
            ],
            [
                'name' => 'Canva',
                'url' => 'https://www.canva.com/brand/kAGSEiMCzKA',
            ],
            [
                'name' => 'Chess.com',
                'url' => 'https://www.chess.com/member/rlcirilo88',
            ],
        ];
    }
}
