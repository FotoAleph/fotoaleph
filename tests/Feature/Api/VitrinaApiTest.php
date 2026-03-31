<?php

namespace Tests\Feature\Api;

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

    public function test_coordinator_assigned_to_tenant_can_create_vitrinas(): void
    {
        $user = User::factory()->create(['role' => 'coordinador']);
        $tenant = Tenant::create(['razon_social' => 'Casa Angel']);
        $tenant->users()->attach($user->id);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/tenants/{$tenant->id}/vitrinas", [
            'nombre' => 'Nueva Vitrina',
            'descripcion' => 'Descripcion de prueba',
            'imagen' => '/nueva.jpg',
            'categoria' => 'General',
            'grupo' => 'Rosa',
            'nivel' => 5,
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
            'imagen' => $imagen,
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
