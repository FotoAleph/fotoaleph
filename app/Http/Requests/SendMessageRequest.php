<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SendMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255|min:3',
            'telefono' => 'required|string|max:20|min:7|regex:/^[0-9\s\+\-\(\)]+$/',
            'mensaje' => 'required|string|max:5000|min:10',
            'tenant_id' => 'required|integer|exists:tenants,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser texto.',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',

            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.string' => 'El teléfono debe ser texto.',
            'telefono.max' => 'El teléfono no puede exceder 20 caracteres.',
            'telefono.min' => 'El teléfono debe tener al menos 7 caracteres.',
            'telefono.regex' => 'El teléfono solo puede contener números, espacios, guiones, signos de más y paréntesis.',
            
            'mensaje.required' => 'El mensaje es obligatorio.',
            'mensaje.string' => 'El mensaje debe ser texto.',
            'mensaje.max' => 'El mensaje no puede exceder 5000 caracteres.',
            'mensaje.min' => 'El mensaje debe tener al menos 10 caracteres.',
            
            'tenant_id.required' => 'El ID del tenant es obligatorio.',
            'tenant_id.integer' => 'El ID del tenant debe ser un número.',
            'tenant_id.exists' => 'El tenant especificado no existe.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Error en la validación del mensaje.',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
