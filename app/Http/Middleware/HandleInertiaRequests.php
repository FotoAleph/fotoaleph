<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
                'tenants' => fn () => $this->sharedTenants($request),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'translations' => function () {
        return cache()->rememberForever('translations_' . app()->getLocale(), function () {
            return file_get_contents(lang_path(app()->getLocale() . '.json'));
        });
    },];
    }

    private function sharedTenants(Request $request): array
    {
        $user = $request->user();

        if (! $user) {
            return [];
        }

        $query = Tenant::query()->select(['id', 'razon_social', 'database_connection'])->orderBy('razon_social');

        if ($user->role !== 'admin') {
            $query->whereHas('users', fn ($tenantQuery) => $tenantQuery->where('users.id', $user->id));
        }

        return $query->get()->map(fn (Tenant $tenant) => [
            'id' => $tenant->id,
            'razon_social' => $tenant->razon_social,
            'database_connection' => $tenant->databaseConnectionName(),
        ])->all();
    }

}
