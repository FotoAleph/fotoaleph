<?php

namespace Tests\Unit;

use App\Models\Tenant;
use App\Support\Tenants\TenantConnectionResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantConnectionResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_central_connection_when_no_tenant_is_set(): void
    {
        $resolver = app(TenantConnectionResolver::class);

        $this->assertSame('tenant_central', $resolver->connectionFor());
    }

    public function test_it_resolves_connection_from_a_tenant_model(): void
    {
        $tenant = Tenant::create([
            'razon_social' => 'Casa Angel',
            'database_connection' => 'tenant_casa_angel',
        ]);

        $resolver = app(TenantConnectionResolver::class);

        $this->assertSame('tenant_casa_angel', $resolver->connectionFor($tenant));
    }

    public function test_it_resolves_connection_from_current_tenant_context(): void
    {
        $tenant = Tenant::create([
            'razon_social' => 'Vidrios y Estructuras JyM',
            'database_connection' => 'tenant_jym',
        ]);

        $resolver = app(TenantConnectionResolver::class);
        $resolver->setCurrentTenant($tenant);

        $this->assertSame('tenant_jym', $resolver->connectionFor());

        $resolver->forgetCurrentTenant();
    }
}
