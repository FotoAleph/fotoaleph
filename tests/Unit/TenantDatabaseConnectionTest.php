<?php

namespace Tests\Unit;

use App\Models\BiotekEstudiante;
use App\Models\Evento;
use App\Models\JymCategoria;
use App\Models\JymGrupo;
use App\Models\Ocasion;
use App\Models\Proyecto;
use App\Models\Taller;
use App\Models\Tematica;
use App\Models\Tenant;
use App\Models\Estudiante;
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
        $this->assertSame('tenant_jym', (new JymGrupo())->getConnectionName());
    }

    public function test_casa_angel_models_use_casa_angel_connection(): void
    {
        $this->assertSame('tenant_casa_angel', (new Evento())->getConnectionName());
        $this->assertSame('tenant_casa_angel', (new Ocasion())->getConnectionName());
        $this->assertSame('tenant_casa_angel', (new Tematica())->getConnectionName());
    }

    public function test_biotek_models_use_biotek_connection(): void
    {
        $this->assertSame('tenant_biotek', (new BiotekEstudiante())->getConnectionName());
        $this->assertSame('tenant_biotek', (new Taller())->getConnectionName());
    }

    public function test_sport_bogota_models_use_sport_connection(): void
    {
        $this->assertSame('tenant_sport_bogota', (new Estudiante())->getConnectionName());
    }
}
