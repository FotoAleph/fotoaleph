<?php

namespace App\Support\Tenants;

use App\Models\Tenant;

class TenantConnectionResolver
{
    private ?Tenant $currentTenant = null;

    public function setCurrentTenant(Tenant $tenant): void
    {
        $this->currentTenant = $tenant;
    }

    public function getCurrentTenant(): ?Tenant
    {
        return $this->currentTenant;
    }

    public function forgetCurrentTenant(): void
    {
        $this->currentTenant = null;
    }

    public function connectionFor(Tenant|string|null $tenant = null, string $fallback = 'tenant_central'): string
    {
        if ($tenant instanceof Tenant) {
            return $tenant->databaseConnectionName();
        }

        if (is_string($tenant) && $tenant !== '') {
            return Tenant::query()
                ->where('razon_social', $tenant)
                ->value('database_connection')
                ?: $fallback;
        }

        return $this->currentTenant?->databaseConnectionName() ?: $fallback;
    }
}
