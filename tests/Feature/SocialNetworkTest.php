<?php

namespace Tests\Feature;

use App\Models\SocialNetworkType;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SocialNetworkTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_social_networks_keep_their_type_relation(): void
    {
        $tenant = Tenant::create(['razon_social' => 'Fotoaleph']);
        $type = SocialNetworkType::create([
            'name' => 'GitHub',
            'base_url' => 'https://github.com/',
            'icon' => 'github.svg',
        ]);

        $tenant->redesSociales()->create([
            'social_network_type_id' => $type->id,
            'url' => 'https://github.com/FotoAleph',
        ]);

        $network = $tenant->redesSociales()->with('socialNetworkType')->first();

        $this->assertSame('GitHub', $network->socialNetworkType->name);
        $this->assertInstanceOf(MorphMany::class, $tenant->aleatoriasRedesSociales());
    }

    public function test_random_social_network_endpoint_returns_tenant_networks_with_type(): void
    {
        $tenant = Tenant::create(['razon_social' => 'Fotoaleph']);
        $type = SocialNetworkType::create([
            'name' => 'GitHub',
            'base_url' => 'https://github.com/',
            'icon' => 'github.svg',
        ]);

        $tenant->redesSociales()->create([
            'social_network_type_id' => $type->id,
            'url' => 'https://github.com/FotoAleph',
        ]);

        $this->getJson("/api/redes-sociales/tenant/{$tenant->id}/aleatorias")
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.url', 'https://github.com/FotoAleph')
            ->assertJsonPath('0.social_network_type.name', 'GitHub')
            ->assertJsonPath('0.social_network_type.icon', 'github.svg');
    }

    public function test_welcome_receives_formatted_random_social_networks(): void
    {
        $tenant = Tenant::create(['razon_social' => 'Fotoaleph']);
        $type = SocialNetworkType::create([
            'name' => 'GitHub',
            'base_url' => 'https://github.com/',
            'icon' => 'github.svg',
        ]);

        $tenant->redesSociales()->create([
            'social_network_type_id' => $type->id,
            'url' => 'https://github.com/FotoAleph',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Welcome')
                ->where('socialNetworks.0.name', 'GitHub')
                ->where('socialNetworks.0.url', 'https://github.com/FotoAleph')
                ->where('socialNetworks.0.icon', 'github.svg')
            );
    }
}
