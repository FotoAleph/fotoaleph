<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_a_successful_response()
    {
        Tenant::create([
            'razon_social' => 'Fotoaleph',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
    }
}
