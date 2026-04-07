<?php

namespace Tests\Feature\Api;

use App\Models\Multimedia;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vitrina;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VitrinaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_only_vitrinas_for_a_given_site(): void
    {
        $casaAngel = Tenant::create(['razon_social' => 'Casa Angel']);
        $casaAngel->sitios()->create([
            'name' => 'Casa Angel',
            'url' => 'casaangel.com',
            'estado' => 'activo',
        ]);

        $jym = Tenant::create(['razon_social' => 'Vidrios y Estructuras JyM']);
        $jym->sitios()->create([
            'name' => 'Vidrios y Estructuras JyM',
            'url' => 'vidriosyestructurasjym.com',
            'estado' => 'activo',
        ]);

        $this->createVitrina($casaAngel, 'Salon Decoracion', '/casa-angel.jpg', 'General', 'Rosa', 1);
        $this->createVitrina($jym, 'Baño Moderno', '/jym.jpg', 'Baños y Adecuaciones', 'General', 2);

        $response = $this->getJson('/api/vitrinas/sitio/casaangel.com');

        $response
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'tenant' => 'Casa Angel',
                'name' => 'Salon Decoracion',
                'img' => '/casa-angel.jpg',
            ])
            ->assertJsonMissing([
                'tenant' => 'Vidrios y Estructuras JyM',
                'name' => 'Baño Moderno',
            ]);
    }

    public function test_it_filters_public_vitrinas_by_tenant_and_category(): void
    {
        $tenant = Tenant::create(['razon_social' => 'Vidrios y Estructuras JyM']);

        $this->createVitrina($tenant, 'Recepcion', '/recepcion.jpg', 'Oficinas', 'Corporativo', 1);
        $this->createVitrina($tenant, 'Local Comercial', '/local.jpg', 'Locativos', 'Comercial', 2);

        $response = $this->getJson("/api/vitrinas/tenant/{$tenant->id}?categoria=Oficinas");

        $response
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'tenant' => 'Vidrios y Estructuras JyM',
                'category' => 'Oficinas',
                'name' => 'Recepcion',
            ])
            ->assertJsonMissing([
                'name' => 'Local Comercial',
            ]);
    }

    public function test_it_orders_public_vitrinas_by_nivel_from_query_string(): void
    {
        $tenant = Tenant::create(['razon_social' => 'Casa Angel']);

        $this->createVitrina($tenant, 'Nivel 3', '/nivel-3.jpg', 'General', 'A', 3);
        $this->createVitrina($tenant, 'Nivel 1', '/nivel-1.jpg', 'General', 'A', 1);
        $this->createVitrina($tenant, 'Nivel 2', '/nivel-2.jpg', 'General', 'A', 2);

        $response = $this->getJson("/api/vitrinas/tenant/{$tenant->id}?direccion_nivel=asc");

        $response
            ->assertOk()
            ->assertJsonCount(3)
            ->assertJsonPath('0.name', 'Nivel 1')
            ->assertJsonPath('0.level', 1)
            ->assertJsonPath('1.name', 'Nivel 2')
            ->assertJsonPath('1.level', 2)
            ->assertJsonPath('2.name', 'Nivel 3')
            ->assertJsonPath('2.level', 3);
    }

    public function test_it_orders_public_vitrinas_by_nivel_desc_from_query_string(): void
    {
        $tenant = Tenant::create(['razon_social' => 'Casa Angel']);

        $this->createVitrina($tenant, 'Nivel 1', '/nivel-1.jpg', 'General', 'A', 1);
        $this->createVitrina($tenant, 'Nivel 3', '/nivel-3.jpg', 'General', 'A', 3);
        $this->createVitrina($tenant, 'Nivel 2', '/nivel-2.jpg', 'General', 'A', 2);

        $response = $this->getJson("/api/vitrinas/tenant/{$tenant->id}?direccion_nivel=desc");

        $response
            ->assertOk()
            ->assertJsonCount(3)
            ->assertJsonPath('0.name', 'Nivel 3')
            ->assertJsonPath('0.level', 3)
            ->assertJsonPath('1.name', 'Nivel 2')
            ->assertJsonPath('1.level', 2)
            ->assertJsonPath('2.name', 'Nivel 1')
            ->assertJsonPath('2.level', 1);
    }

    public function test_coordinator_assigned_to_tenant_can_create_vitrinas(): void
    {
        $user = User::factory()->create(['role' => 'coordinador']);
        $tenant = Tenant::create(['razon_social' => 'Casa Angel']);
        $tenant->users()->attach($user->id);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/tenants/{$tenant->id}/vitrinas", [
            'nombre' => 'Nueva Vitrina',
            'descripcion' => 'Descripcion de prueba',
            'categoria' => 'General',
            'grupo' => 'Rosa',
            'nivel' => 5,
            'items' => [
                [
                    'multimedia_id' => Multimedia::create([
                        'url' => '/nueva-grande.jpg',
                        'preview_url' => '/nueva.jpg',
                        'type' => 'image',
                        'mime_type' => 'image/jpeg',
                    ])->id,
                    'es_portada' => true,
                ],
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonFragment([
                'tenant' => 'Casa Angel',
                'name' => 'Nueva Vitrina',
                'category' => 'General',
            ]);

        $this->assertDatabaseHas('vitrinas', [
            'tenant_id' => $tenant->id,
            'nombre' => 'Nueva Vitrina',
        ]);
    }

    public function test_coordinator_cannot_manage_other_tenant_vitrinas(): void
    {
        $user = User::factory()->create(['role' => 'coordinador']);
        $ownerTenant = Tenant::create(['razon_social' => 'Casa Angel']);
        $otherTenant = Tenant::create(['razon_social' => 'Vidrios y Estructuras JyM']);
        $ownerTenant->users()->attach($user->id);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/tenants/{$otherTenant->id}/vitrinas", [
            'nombre' => 'No Permitida',
            'descripcion' => 'No debe crear',
        ]);

        $response->assertForbidden();
    }

    private function createVitrina(Tenant $tenant, string $nombre, string $imagen, string $categoria, string $grupo, int $nivel): Vitrina
    {
        $vitrina = Vitrina::create([
            'tenant_id' => $tenant->id,
            'nombre' => $nombre,
            'descripcion' => 'Descripcion de '.$nombre,
        ]);

        $multimedia = Multimedia::create([
            'url' => $imagen,
            'preview_url' => $imagen,
            'type' => 'image',
            'mime_type' => 'image/jpeg',
        ]);

        $vitrina->multimedias()->sync([
            $multimedia->id => [
                'orden' => 0,
                'es_portada' => true,
            ],
        ]);

        $vitrina->categoria()->create([
            'nombre' => $categoria,
            'descripcion' => $categoria,
        ]);

        $vitrina->grupo()->create([
            'nombre' => $grupo,
            'descripcion' => $grupo,
        ]);

        $vitrina->nivel()->create([
            'nivel' => $nivel,
        ]);

        return $vitrina;
    }
}
