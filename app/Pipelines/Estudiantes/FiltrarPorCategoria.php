<?php

namespace App\Pipelines\Estudiantes;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class FiltrarPorCategoria
{
    public function __construct(private readonly ?string $categoria) {}

    public function handle(Builder $query, Closure $next): Builder
    {
        $categoria = trim((string) $this->categoria);

        if ($categoria !== '') {
            $query->where('categoria', 'like', "%{$categoria}%");
        }

        return $next($query);
    }
}
