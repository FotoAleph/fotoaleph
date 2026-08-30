<?php

namespace Tests\Feature\Api;

use App\Models\Mensaje;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MensajeControllerTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::create(['razon_social' => 'Test Tenant']);
    }

    /**
     * Test sending a message with valid data.
     */
    public function test_send_message_with_valid_data(): void
    {
        $response = $this->postJson('/api/mensajes', [
            'nombre' => 'Juan Pérez',
            'telefono' => '+57 300 123 4567',
            'mensaje' => 'Este es un mensaje de prueba con contenido válido.',
            'tenant_id' => $this->tenant->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'estado', 'created_at'],
            ])
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Mensaje enviado exitosamente.',
            ]);

        $this->assertDatabaseHas('mensajes', [
            'nombre' => 'Juan Pérez',
            'telefono' => '+57 300 123 4567',
            'tenant_id' => $this->tenant->id,
            'estado' => 'pendiente',
        ]);
    }

    /**
     * Test sending a message with missing required fields.
     */
    public function test_send_message_with_missing_fields(): void
    {
        $response = $this->postJson('/api/mensajes', [
            'nombre' => 'Juan Pérez',
            'tenant_id' => $this->tenant->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'success' => false,
                'message' => 'Error en la validación del mensaje.',
            ])
            ->assertJsonStructure([
                'errors' => [ 'telefono', 'mensaje'],
            ]);
    }

   
    /**
     * Test sending a message with invalid phone number.
     */
    public function test_send_message_with_invalid_phone(): void
    {
        $response = $this->postJson('/api/mensajes', [
            'nombre' => 'Juan Pérez',
            'telefono' => 'abc123xyz', // No contiene números válidos
            'mensaje' => 'Este es un mensaje de prueba con contenido válido.',
            'tenant_id' => $this->tenant->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.telefono', ['El teléfono solo puede contener números, espacios, guiones, signos de más y paréntesis.']);
    }

    /**
     * Test sending a message with short message content.
     */
    public function test_send_message_with_short_message(): void
    {
        $response = $this->postJson('/api/mensajes', [
            'nombre' => 'Juan Pérez',
            'telefono' => '+57 300 123 4567',
            'mensaje' => 'Corto', // Menos de 10 caracteres
            'tenant_id' => $this->tenant->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.mensaje', ['El mensaje debe tener al menos 10 caracteres.']);
    }

    /**
     * Test sending a message with non-existent tenant.
     */
    public function test_send_message_with_invalid_tenant(): void
    {
        $response = $this->postJson('/api/mensajes', [
            'nombre' => 'Juan Pérez',
            'telefono' => '+57 300 123 4567',
            'mensaje' => 'Este es un mensaje de prueba con contenido válido.',
            'tenant_id' => 9999,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.tenant_id', ['El tenant especificado no existe.']);
    }

    /**
     * Test sending a message with authenticated user.
     */
    public function test_send_message_with_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/mensajes', [
                'nombre' => 'Juan Pérez',
                'telefono' => '+57 300 123 4567',
                'mensaje' => 'Este es un mensaje de prueba con contenido válido.',
                'tenant_id' => $this->tenant->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Mensaje enviado exitosamente.',
            ]);

        $this->assertDatabaseHas('mensajes', [
            'user_id' => $user->id,
            'email' => 'juan@example.com',
        ]);
    }

    /**
     * Test retrieving all messages.
     */
    public function test_index_messages(): void
    {
        $user = User::factory()->create();
        Mensaje::factory()
            ->count(5)
            ->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($user)
            ->getJson('/api/mensajes');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data' => [
                        '*' => ['id', 'nombre',  'telefono', 'mensaje', 'estado'],
                    ],
                    'current_page',
                    'total',
                ],
            ])
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Mensajes obtenidos exitosamente.',
            ]);
    }

    /**
     * Test retrieving all messages without authentication.
     */
    public function test_index_messages_requires_authentication(): void
    {
        $response = $this->getJson('/api/mensajes');

        $response->assertStatus(401);
    }

    /**
     * Test retrieving a specific message.
     */
    public function test_show_message(): void
    {
        $user = User::factory()->create();
        $mensaje = Mensaje::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($user)
            ->getJson("/api/mensajes/{$mensaje->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'nombre',  'telefono', 'mensaje', 'estado'],
            ])
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Mensaje obtenido exitosamente.',
                'id' => $mensaje->id,
            ]);
    }

    /**
     * Test retrieving a specific message requires authentication.
     */
    public function test_show_message_requires_authentication(): void
    {
        $mensaje = Mensaje::factory()->create(['tenant_id' => $this->tenant->id]);
        $response = $this->getJson("/api/mensajes/{$mensaje->id}");

        $response->assertStatus(401);
    }

    /**
     * Test retrieving a non-existent message.
     */
    public function test_show_non_existent_message(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->getJson('/api/mensajes/9999');

        $response->assertStatus(404)
            ->assertJsonFragment([
                'success' => false,
                'message' => 'El mensaje no fue encontrado.',
            ]);
    }

    /**
     * Test updating message status.
     */
    public function test_update_message_status(): void
    {
        $user = User::factory()->create();
        $mensaje = Mensaje::factory()
            ->create(['tenant_id' => $this->tenant->id, 'estado' => 'pendiente']);

        $response = $this->actingAs($user)
            ->patchJson("/api/mensajes/{$mensaje->id}", [
                'estado' => 'leído',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Mensaje actualizado exitosamente.',
                'estado' => 'leído',
            ]);

        $this->assertDatabaseHas('mensajes', [
            'id' => $mensaje->id,
            'estado' => 'leído',
        ]);
    }

    /**
     * Test updating message requires authentication.
     */
    public function test_update_message_requires_authentication(): void
    {
        $mensaje = Mensaje::factory()
            ->create(['tenant_id' => $this->tenant->id, 'estado' => 'pendiente']);

        $response = $this->patchJson("/api/mensajes/{$mensaje->id}", [
            'estado' => 'leído',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test updating message with invalid status.
     */
    public function test_update_message_with_invalid_status(): void
    {
        $user = User::factory()->create();
        $mensaje = Mensaje::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($user)
            ->patchJson("/api/mensajes/{$mensaje->id}", [
                'estado' => 'invalido',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.estado', ['El estado debe ser pendiente, leído o respondido.']);
    }

    /**
     * Test deleting a message.
     */
    public function test_destroy_message(): void
    {
        $user = User::factory()->create();
        $mensaje = Mensaje::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($user)
            ->deleteJson("/api/mensajes/{$mensaje->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Mensaje eliminado exitosamente.',
            ]);

        $this->assertDatabaseMissing('mensajes', [
            'id' => $mensaje->id,
        ]);
    }

    /**
     * Test deleting a message requires authentication.
     */
    public function test_destroy_message_requires_authentication(): void
    {
        $mensaje = Mensaje::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->deleteJson("/api/mensajes/{$mensaje->id}");

        $response->assertStatus(401);
    }

    /**
     * Test deleting a non-existent message.
     */
    public function test_destroy_non_existent_message(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->deleteJson('/api/mensajes/9999');

        $response->assertStatus(404)
            ->assertJsonFragment([
                'success' => false,
                'message' => 'El mensaje no fue encontrado.',
            ]);
    }
}
