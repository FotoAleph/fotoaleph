<?php

namespace App\Support\Api;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class IntegerCounterMutation
{
    public function apply(Model $model, string $field, string $operation, int $value): int
    {
        $current = (int) ($model->getAttribute($field) ?? 0);

        if (in_array($operation, ['increment', 'decrement'], true) && $value < 1) {
            throw ValidationException::withMessages([
                $field => ['El valor debe ser un entero mayor o igual a 1 para incrementar o decrementar.'],
            ]);
        }

        if ($operation === 'set' && $value < 0) {
            throw ValidationException::withMessages([
                $field => ['El valor debe ser un entero positivo o cero.'],
            ]);
        }

        $next = match ($operation) {
            'increment' => $current + $value,
            'decrement' => $current - $value,
            'set' => $value,
            default => throw ValidationException::withMessages([
                'operation' => ['La operacion solicitada no es valida.'],
            ]),
        };

        if ($next < 0) {
            throw ValidationException::withMessages([
                $field => ['El valor no puede quedar por debajo de cero.'],
            ]);
        }

        $model->forceFill([$field => $next])->save();

        return $next;
    }
}