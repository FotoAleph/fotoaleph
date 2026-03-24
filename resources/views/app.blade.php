<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        
        {{-- Inter font from rsms.me --}}
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />

        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        
        {{-- Exponer función route() de Laravel como global en JavaScript --}}
        <script>
            window.route = function(name, params = {}) {
                // Helper para reemplazar parámetros en URLs
                const replaceParams = (url) => {
                    if (!params || typeof params !== 'object') return url;
                    
                    Object.entries(params).forEach(([key, value]) => {
                        if (value !== undefined && value !== null) {
                            // Reemplazar {id}, {user}, {tenant}, etc.
                            url = url.replace(new RegExp(`\\{${key}\\}`), value);
                            // También reemplazar parámetros opcionales {id?}
                            url = url.replace(new RegExp(`\\{${key}\\?\\}`), value);
                        }
                    });
                    
                    // Remover parámetros opcionales sin valor
                    url = url.replace(/\/\{[^}]+\??\}/g, '');
                    
                    return url;
                };
                
                const baseUrl = '{{ url("/") }}';
                
                // Mapeo de rutas compiladas desde Laravel
                const routeMap = {
                    'dashboard': '/dashboard',
                    'users.index': '/users',
                    'users.create': '/users/create',
                    'users.store': '/users',
                    'users.show': '/users/{user}',
                    'users.edit': '/users/{user}/edit',
                    'users.update': '/users/{user}',
                    'users.destroy': '/users/{user}',
                    'customers.index': '/customers',
                    'tenants.index': '/tenants',
                    'tenants.create': '/tenants/create',
                    'tenants.store': '/tenants',
                    'tenants.show': '/tenants/{tenant}',
                    'tenants.edit': '/tenants/{tenant}/edit',
                    'tenants.update': '/tenants/{tenant}',
                    'tenants.destroy': '/tenants/{tenant}',
                    'pqrs.index': '/pqrs',
                    'cotizaciones.index': '/cotizaciones',
                };
                
                let url = routeMap[name];
                
                if (!url) {
                    console.warn(`Route '${name}' not found in route map`);
                    return '#';
                }
                
                url = baseUrl + url;
                return replaceParams(url);
            };
        </script>
        
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
