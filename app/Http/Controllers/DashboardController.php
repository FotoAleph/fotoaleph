<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $role = $user->role ?? 'cliente'; // Valor por defecto si role es null

        // Configuración base
        $config = $this->getDashboardConfigForRole($role);

        return Inertia::render('Dashboard', [
            'breadcrumbs' => $config['breadcrumbs'],
            'sidebar_items' => $config['sidebar_items'],
            'layout' => $config['layout'],
            'stats' => $config['stats'],
            'user_role' => $role,
        ]);
    }

    private function getDashboardConfigForRole(string $role): array
    {
        $baseConfig = [
            'breadcrumbs' => [
                [
                    'title' => __('Dashboard'),
                    'href' => route('dashboard'),
                ],
            ],
            'layout' => 'AppLayout', // Layout por defecto
            'stats' => [],
        ];

        switch ($role) {
            case 'admin':
                return array_merge($baseConfig, [
                    'layout' => 'AdminLayout',
                    'sidebar_items' => $this->getAdminSidebarItems(),
                    'stats' => $this->getAdminStats(),
                ]);

            case 'coordinador':
                return array_merge($baseConfig, [
                    'layout' => 'CoordinadorLayout',
                    'sidebar_items' => $this->getCoordinadorSidebarItems(),
                    'stats' => $this->getCoordinadorStats(),
                ]);

            case 'cliente':
            default:
                return array_merge($baseConfig, [
                    'layout' => 'ClientLayout',
                    'sidebar_items' => $this->getClientSidebarItems(),
                    'stats' => $this->getClientStats(),
                ]);
        }
    }

    private function getAdminSidebarItems(): array
    {
        return [
            [
                'title' => 'Dashboard',
                'href' => route('dashboard'),
                'icon' => 'LayoutGrid',
            ],
            [
                'title' => 'Tenants',
                'href' => route('tenants.index'),
                'icon' => 'Building',
            ],
            [
                'title' => 'PQRs',
                'href' => route('pqrs.index'),
                'icon' => 'MessageSquare',
            ],
            [
                'title' => 'Cotizaciones',
                'href' => route('cotizaciones.index'),
                'icon' => 'FileText',
            ],
            [
                'title' => 'Configuración',
                'href' => '/settings',
                'icon' => 'Settings',
            ],
        ];
    }

    private function getCoordinadorSidebarItems(): array
    {
        return [
            [
                'title' => 'Dashboard',
                'href' => route('dashboard'),
                'icon' => 'LayoutGrid',
            ],
            [
                'title' => 'Mis PQRs',
                'href' => route('pqrs.index'),
                'icon' => 'MessageSquare',
            ],
            [
                'title' => 'Cotizaciones',
                'href' => route('cotizaciones.index'),
                'icon' => 'FileText',
            ],
            [
                'title' => 'Perfil',
                'href' => '/settings/profile',
                'icon' => 'User',
            ],
        ];
    }

    private function getClientSidebarItems(): array
    {
        return [
            [
                'title' => 'Dashboard',
                'href' => route('dashboard'),
                'icon' => 'LayoutGrid',
            ],
            [
                'title' => 'Mis PQRs',
                'href' => route('pqrs.index'),
                'icon' => 'MessageSquare',
            ],
            [
                'title' => 'Cotizaciones',
                'href' => route('cotizaciones.create'),
                'icon' => 'Plus',
            ],
            [
                'title' => 'Perfil',
                'href' => route('profile.edit'),
                'icon' => 'User',
            ],
        ];
    }

    private function getAdminStats(): array
    {
        return [
            'total_users' => \App\Models\User::count(),
            'total_tenants' => \App\Models\Tenant::count(),
            'total_pqrs' => \App\Models\Pqr::count(),
            'total_cotizaciones' => \App\Models\Cotizacion::count(),
        ];
    }

    private function getCoordinadorStats(): array
    {
        $user = auth()->user();
        return [
            'my_pqrs' => \App\Models\Pqr::where('user_id', $user->id)->count(),
            'pending_cotizaciones' => \App\Models\Cotizacion::where('status', 'pending')->count(),
        ];
    }

    private function getClientStats(): array
    {
        $user = auth()->user();
        return [
            'my_pqrs' => \App\Models\Pqr::where('user_id', $user->id)->count(),
            'my_cotizaciones' => \App\Models\Cotizacion::where('user_id', $user->id)->count(),
        ];
    }
}
