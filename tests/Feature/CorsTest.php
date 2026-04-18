<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class CorsTest extends TestCase
{
    public function test_preflight_request_returns_cors_headers_for_allowed_origin(): void
    {
        config()->set('cors.allowed_origins', ['http://frontend.test']);
        config()->set('cors.allowed_methods', ['GET', 'POST', 'OPTIONS']);
        config()->set('cors.allowed_headers', ['Accept', 'Authorization', 'Content-Type', 'Origin']);
        config()->set('cors.paths', ['api/*']);

        $response = $this->call('OPTIONS', '/api/login', server: [
            'HTTP_ORIGIN' => 'http://frontend.test',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
            'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'Authorization, Content-Type',
        ]);

        $response->assertNoContent();
        $response->assertHeader('Access-Control-Allow-Origin', 'http://frontend.test');
        $response->assertHeader('Access-Control-Allow-Methods');
        $response->assertHeader('Access-Control-Allow-Headers');

        self::assertStringContainsString(
            'POST',
            (string) $response->headers->get('Access-Control-Allow-Methods')
        );

        self::assertStringContainsString(
            'authorization',
            (string) $response->headers->get('Access-Control-Allow-Headers')
        );
    }

    public function test_web_routes_do_not_receive_cors_headers_when_path_is_not_configured(): void
    {
        config()->set('cors.allowed_origins', ['http://frontend.test']);
        config()->set('cors.allowed_methods', ['GET', 'POST', 'OPTIONS']);
        config()->set('cors.allowed_headers', ['Accept', 'Authorization', 'Content-Type', 'Origin']);
        config()->set('cors.paths', ['api/*']);

        $response = $this->get('/up', [
            'HTTP_ORIGIN' => 'http://evil.test',
        ]);

        self::assertNull($response->headers->get('Access-Control-Allow-Origin'));
    }
}
