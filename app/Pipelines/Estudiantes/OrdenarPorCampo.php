<?php

namespace App\Pipelines\Estudiantes;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class OrdenarPorCampo
{
    public function __construct(
        private readonly string $campo = 'created_at',
        private readonly string $direccion = 'desc',
    ) {}

    public function handle(Builder $query, Closure $next): Builder
    {
        $campo = $this->normalizeField($this->campo);
        $direccion = strtolower($this->direccion) === 'asc' ? 'asc' : 'desc';

        $query->orderBy($campo, $direccion);

        return $next($query);
    }

    private function normalizeField(string $field): string
    {
        return in_array($field, ['nombre', 'categoria', 'created_at'], true)
            ? $field
            : 'created_at';
    }
}
