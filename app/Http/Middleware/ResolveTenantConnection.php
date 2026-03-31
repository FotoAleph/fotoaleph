<?php

namespace App\Http\Middleware;

use App\Models\Sitio;
use App\Models\Tenant;
use App\Support\Tenants\TenantConnectionResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantConnection
{
    public function __construct(
        private readonly TenantConnectionResolver $resolver,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->resolveTenantFromRequest($request);

        if ($tenant) {
            $this->resolver->setCurrentTenant($tenant);
        }

        try {
            return $next($request);
        } finally {
            $this->resolver->forgetCurrentTenant();
        }
    }

    private function resolveTenantFromRequest(Request $request): ?Tenant
    {
        $tenant = $request->route('tenant');

        if ($tenant instanceof Tenant) {
            return $tenant;
        }

        if (is_scalar($tenant)) {
            return Tenant::query()->find($tenant);
        }

        $site = $request->route('site');

        if (! is_scalar($site)) {
            return null;
        }

        return Sitio::query()
            ->with('tenant')
            ->where('url', (string) $site)
            ->first()
            ?->tenant;
    }
}
