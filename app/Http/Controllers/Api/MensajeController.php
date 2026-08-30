<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendMessageRequest;
use App\Models\Mensaje;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class MensajeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $mensajes = Mensaje::with(['tenant', 'user'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return response()->json([
                'success' => true,
                'message' => 'Mensajes obtenidos exitosamente.',
                'data' => $mensajes,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener mensajes', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los mensajes.',
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SendMessageRequest $request): JsonResponse
    {
        return $this->sendMessage($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $mensaje = Mensaje::with(['tenant', 'user'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Mensaje obtenido exitosamente.',
                'data' => $mensaje,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'El mensaje no fue encontrado.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al obtener mensaje', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el mensaje.',
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $mensaje = Mensaje::findOrFail($id);

            $request->validate([
                'estado' => 'required|in:pendiente,leído,respondido',
            ], [
                'estado.required' => 'El estado es obligatorio.',
                'estado.in' => 'El estado debe ser pendiente, leído o respondido.',
            ]);

            $mensaje->update(['estado' => $request->estado]);

            return response()->json([
                'success' => true,
                'message' => 'Mensaje actualizado exitosamente.',
                'data' => $mensaje,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'El mensaje no fue encontrado.',
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en la validación.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al actualizar mensaje', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el mensaje.',
            ], 500);
        }
    }

    /**
     * Remove the specified resource in storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $mensaje = Mensaje::findOrFail($id);
            $mensaje->delete();

            return response()->json([
                'success' => true,
                'message' => 'Mensaje eliminado exitosamente.',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'El mensaje no fue encontrado.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al eliminar mensaje', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el mensaje.',
            ], 500);
        }
    }

    /**
     * Send a new message.
     *
     * @param SendMessageRequest $request
     * @return JsonResponse
     */
    public function sendMessage(SendMessageRequest $request): JsonResponse
    {
        try {
            // Validación de datos ya realizada por SendMessageRequest
            $validatedData = $request->validated();

            // Verificar que el tenant existe
            $tenant = Tenant::findOrFail($validatedData['tenant_id']);

            // Crear el mensaje
            $mensaje = Mensaje::create([
                'nombre' => $validatedData['nombre'],
                'telefono' => $validatedData['telefono'],
                'mensaje' => $validatedData['mensaje'],
                'tenant_id' => $validatedData['tenant_id'],
                'user_id' => $request->user()?->id,
                'estado' => 'pendiente',
            ]);

            Log::info('Nuevo mensaje creado', [
                'mensaje_id' => $mensaje->id,
                'tenant_id' => $tenant->id,
                
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mensaje enviado exitosamente.',
                'data' => [
                    'id' => $mensaje->id,
                    'estado' => $mensaje->estado,
                    'created_at' => $mensaje->created_at,
                ],
            ], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Tenant no encontrado', ['tenant_id' => $validatedData['tenant_id'] ?? 'unknown']);
            return response()->json([
                'success' => false,
                'message' => 'El tenant especificado no existe.',
            ], 404);
        } catch (QueryException $e) {
            Log::error('Error en base de datos al guardar mensaje', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el mensaje en la base de datos.',
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error inesperado al enviar mensaje', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error inesperado al procesar el mensaje.',
            ], 500);
        }
    }
}
