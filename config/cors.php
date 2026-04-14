<?php

declare(strict_types=1);

$allowedOrigins = array_values(array_filter(array_map(
    static fn (string $origin): string => trim($origin),
    explode(',', (string) env('CORS_ALLOWED_ORIGINS', 'http://localhost,http://localhost:3000,http://127.0.0.1,http://127.0.0.1:3000'))
)));

$allowedMethods = array_values(array_filter(array_map(
    static fn (string $method): string => strtoupper(trim($method)),
    explode(',', (string) env('CORS_ALLOWED_METHODS', 'GET,POST,PUT,PATCH,DELETE,OPTIONS'))
)));

$allowedHeaders = array_values(array_filter(array_map(
    static fn (string $header): string => trim($header),
    explode(',', (string) env('CORS_ALLOWED_HEADERS', 'Accept,Authorization,Content-Type,Origin,X-Requested-With'))
)));

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => $allowedMethods,

    'allowed_origins' => $allowedOrigins,

    'allowed_origins_patterns' => [],

    'allowed_headers' => $allowedHeaders,

    'exposed_headers' => [],

    'max_age' => (int) env('CORS_MAX_AGE', 0),

    // Keep this disabled for bearer-token auth; enable only if you switch to cookie-based auth.
    'supports_credentials' => env('CORS_SUPPORTS_CREDENTIALS', false),
];
