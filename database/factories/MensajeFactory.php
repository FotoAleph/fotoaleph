<?php

namespace Database\Factories;

use App\Models\Mensaje;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mensaje>
 */
class MensajeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Mensaje::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->name(),
            'telefono' => $this->faker->phoneNumber(),
            'mensaje' => $this->faker->paragraph(3),
            'tenant_id' => Tenant::create(['razon_social' => $this->faker->company()])->id,
            'user_id' => null,
            'estado' => $this->faker->randomElement(['pendiente', 'leído', 'respondido']),
        ];
    }

    /**
     * State to set the message as pending.
     */
    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'estado' => 'pendiente',
        ]);
    }

    /**
     * State to set the message as read.
     */
    public function read(): static
    {
        return $this->state(fn(array $attributes) => [
            'estado' => 'leído',
        ]);
    }

    /**
     * State to set the message as answered.
     */
    public function answered(): static
    {
        return $this->state(fn(array $attributes) => [
            'estado' => 'respondido',
        ]);
    }

    /**
     * State to associate with a user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn(array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * State to associate with a tenant.
     */
    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn(array $attributes) => [
            'tenant_id' => $tenant->id,
        ]);
    }
}
