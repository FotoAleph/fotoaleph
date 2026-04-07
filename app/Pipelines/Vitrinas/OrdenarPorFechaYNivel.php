<?php

namespace App\Pipelines\Vitrinas;

use Closure;
use Illuminate\Support\Collection;

class OrdenarPorFechaYNivel
{
    public function __construct(private readonly string $direccionFecha = 'desc', private readonly string $direccionNivel = 'asc') {}

    public function handle(Collection $vitrinas, Closure $next): Collection
    {
        $direccionNivel = $this->normalizeDirection($this->direccionNivel);
        $direccionFecha = $this->normalizeDirection($this->direccionFecha);

        $ordenadas = $vitrinas->sort(function (mixed $izquierda, mixed $derecha) use ($direccionNivel, $direccionFecha): int {
            $comparacionNivel = $this->compareValues(
                $this->resolveNivel($izquierda, $direccionNivel),
                $this->resolveNivel($derecha, $direccionNivel),
                $direccionNivel,
            );

            if ($comparacionNivel !== 0) {
                return $comparacionNivel;
            }

            $comparacionFecha = $this->compareValues(
                $this->resolveTimestamp($izquierda),
                $this->resolveTimestamp($derecha),
                $direccionFecha,
            );

            if ($comparacionFecha !== 0) {
                return $comparacionFecha;
            }

            return $this->compareValues(
                (int) data_get($izquierda, 'id', 0),
                (int) data_get($derecha, 'id', 0),
                $direccionFecha,
            );
        });

        return $next($ordenadas);
    }

    private function resolveNivel(mixed $vitrina, string $direction): int
    {
        $nivel = data_get($vitrina, 'nivel.nivel');

        if (is_numeric($nivel)) {
            return (int) $nivel;
        }

        return $direction === 'desc' ? PHP_INT_MIN : PHP_INT_MAX;
    }

    private function resolveTimestamp(mixed $vitrina): int
    {
        $createdAt = data_get($vitrina, 'created_at');

        if ($createdAt instanceof \DateTimeInterface) {
            return $createdAt->getTimestamp();
        }

        return is_string($createdAt) ? strtotime($createdAt) ?: 0 : 0;
    }

    private function compareValues(int $left, int $right, string $direction): int
    {
        if ($left === $right) {
            return 0;
        }

        if ($direction === 'desc') {
            return $right <=> $left;
        }

        return $left <=> $right;
    }

    private function normalizeDirection(string $direction): string
    {
        return strtolower($direction) === 'desc' ? 'desc' : 'asc';
    }
}
