<?php

namespace Tests\Unit;

use App\Models\Categoria;
use App\Models\Grupo;
use App\Models\Nivel;
use App\Models\Vitrina;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class VitrinaFilterPipelineTest extends TestCase
{
    public function test_it_filters_by_categoria_and_grupo_and_orders_by_fecha_and_nivel(): void
    {
        Carbon::setTestNow('2026-03-30 10:00:00');

        $primera = new Vitrina(['nombre' => 'Primera']);
        $primera->id = 1;
        $primera->created_at = Carbon::parse('2026-03-20 08:00:00');
        $primera->setRelation('categoria', new Categoria(['nombre' => 'Arte']));
        $primera->setRelation('grupo', new Grupo(['nombre' => 'A']));
        $primera->setRelation('nivel', new Nivel(['nivel' => 2]));

        $segunda = new Vitrina(['nombre' => 'Segunda']);
        $segunda->id = 2;
        $segunda->created_at = Carbon::parse('2026-03-25 08:00:00');
        $segunda->setRelation('categoria', new Categoria(['nombre' => 'Arte']));
        $segunda->setRelation('grupo', new Grupo(['nombre' => 'A']));
        $segunda->setRelation('nivel', new Nivel(['nivel' => 1]));

        $tercera = new Vitrina(['nombre' => 'Tercera']);
        $tercera->id = 3;
        $tercera->created_at = Carbon::parse('2026-03-29 08:00:00');
        $tercera->setRelation('categoria', new Categoria(['nombre' => 'Foto']));
        $tercera->setRelation('grupo', new Grupo(['nombre' => 'B']));
        $tercera->setRelation('nivel', new Nivel(['nivel' => 3]));

        $resultado = Vitrina::filtrar(
            [
                'categoria' => 'Arte',
                'grupo' => 'A',
                'direccion_fecha' => 'desc',
                'direccion_nivel' => 'asc',
            ],
            new Collection([$primera, $segunda, $tercera]),
        );

        $this->assertCount(2, $resultado);
        $this->assertSame(['Segunda', 'Primera'], $resultado->pluck('nombre')->all());
    }
}
