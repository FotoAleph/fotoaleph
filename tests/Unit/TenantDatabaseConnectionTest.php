<?php

namespace Tests\Unit;

use App\Models\Evento;
use App\Models\JymCategoria;
use App\Models\Ocasion;
use App\Models\Proyecto;
use App\Models\Tematica;
use App\Models\Tenant;
use Tests\TestCase;

class TenantDatabaseConnectionTest extends TestCase
{
    public function test_tenant_defaults_to_central_connection_name(): void
    {
        $tenant = new Tenant([
            'razon_social' => 'Tenant General',
        ]);

        $this->assertSame('tenant_central', $tenant->databaseConnectionName());
    }

    public function test_tenant_returns_assigned_connection_name(): void
    {
        $tenant = new Tenant([
            'razon_social' => 'Casa Angel',
            'database_connection' => 'tenant_casa_angel',
        ]);

        $this->assertSame('tenant_casa_angel', $tenant->databaseConnectionName());
    }

    public function test_jym_models_use_jym_connection(): void
    {
        $this->assertSame('tenant_jym', (new Proyecto())->getConnectionName());
        $this->assertSame('tenant_jym', (new JymCategoria())->getConnectionName());
    }

    public function test_casa_angel_models_use_casa_angel_connection(): void
    {
        $this->assertSame('tenant_casa_angel', (new Evento())->getConnectionName());
        $this->assertSame('tenant_casa_angel', (new Ocasion())->getConnectionName());
        $this->assertSame('tenant_casa_angel', (new Tematica())->getConnectionName());
    }
}
