<?php

namespace App\Pipelines\Vitrinas\Concerns;

use Illuminate\Database\Eloquent\Model;

trait InterpretsFilterValue
{
    protected function normalizeFilterValue(mixed $value): array
    {
        if ($value instanceof Model) {
            return [
                'id' => $value->getKey(),
                'nombre' => $value->getAttribute('nombre'),
            ];
        }

        if (is_array($value)) {
            return [
                'id' => $value['id'] ?? null,
                'nombre' => $value['nombre'] ?? null,
            ];
        }

        if (is_numeric($value)) {
            return [
                'id' => (int) $value,
                'nombre' => null,
            ];
        }

        if (is_string($value) && $value !== '') {
            return [
                'id' => null,
                'nombre' => $value,
            ];
        }

        return [
            'id' => null,
            'nombre' => null,
        ];
    }
}
