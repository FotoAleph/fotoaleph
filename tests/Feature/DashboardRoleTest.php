<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_dashboard_with_admin_layout()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('layout')
            ->where('layout', 'AdminLayout')
            ->has('stats')
            ->has('sidebar_items')
        );
    }

    public function test_coordinador_can_access_dashboard_with_employee_layout()
    {
        $coordinador = User::factory()->create(['role' => 'coordinador']);

        $response = $this->actingAs($coordinador)->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('layout')
            ->where('layout', 'EmployeeLayout')
            ->has('stats')
            ->has('sidebar_items')
        );
    }

    public function test_client_can_access_dashboard_with_client_layout()
    {
        $client = User::factory()->create(['role' => 'cliente']);

        $response = $this->actingAs($client)->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('layout')
            ->where('layout', 'ClientLayout')
            ->has('stats')
            ->has('sidebar_items')
        );
    }

    public function test_admin_sees_admin_sidebar_items()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->has('sidebar_items')
            ->where('sidebar_items', function ($items) {
                return collect($items)->contains('title', 'Dashboard') &&
                       collect($items)->contains('title', 'Tenants') &&
                       collect($items)->contains('title', 'PQRs') &&
                       collect($items)->contains('title', 'Cotizaciones');
            })
        );
    }

    public function test_coordinador_sees_employee_sidebar_items()
    {
        $coordinador = User::factory()->create(['role' => 'coordinador']);

        $response = $this->actingAs($coordinador)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->has('sidebar_items')
            ->where('sidebar_items', function ($items) {
                return collect($items)->contains('title', 'Mis PQRs') &&
                       collect($items)->contains('title', 'Cotizaciones') &&
                       !collect($items)->contains('title', 'Usuarios');
            })
        );
    }

    public function test_client_sees_client_sidebar_items()
    {
        $client = User::factory()->create(['role' => 'cliente']);

        $response = $this->actingAs($client)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->has('sidebar_items')
            ->where('sidebar_items', function ($items) {

                return collect($items)->contains('title', 'Dashboard') &&
                       collect($items)->contains('title', 'Mis PQRs') &&
                       collect($items)->contains('title', 'Cotizaciones');
            })
        );
    }
}