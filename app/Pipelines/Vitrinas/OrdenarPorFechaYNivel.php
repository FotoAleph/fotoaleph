<?php

namespace App\Pipelines\Vitrinas;

use Closure;
use Illuminate\Support\Collection;

class OrdenarPorFechaYNivel
{
    public function __construct(private readonly string $direccionFecha = 'desc', private readonly string $direccionNivel = 'asc') {}

    public function handle(Collection $vitrinas, Closure $next): Collection
    {
        $ordenadas = $vitrinas->sortBy([
            ['created_at', $this->normalizeDirection($this->direccionFecha)],
            [fn ($vitrina) => data_get($vitrina, 'nivel.nivel', PHP_INT_MAX), $this->normalizeDirection($this->direccionNivel)],
        ]);

        return $next($ordenadas);
    }

    private function normalizeDirection(string $direction): string
    {
        return strtolower($direction) === 'desc' ? 'desc' : 'asc';
    }
}
