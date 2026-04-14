<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Color;
use App\Models\Evento;
use App\Models\Multimedia;
use App\Models\Ocasion;
use App\Models\Tematica;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CasaAngelTenantSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->galleryItems() as $item) {


            $evento = Evento::query()->firstOrCreate(
                ['codigo' => $item['codigo'] ],
                [
                    'descripcion' => null,
                    'nombre' => $item['nombre'],
                    'fecha_evento' => $this->dateParse($item['fecha']),
                    'entregado' => $item['estado']==='Entregado' ? $this->addFortyDays($item['fecha']) : null,
                    'codigo' => $item['codigo'],
                ],
            );

            $evento->forceFill([
                'descripcion' => $evento->descripcion ?: null,
                'fecha_evento' => $evento->fecha_evento ?: $item['fecha'],
                'entregado' => $item['estado']=='Entregado' ? now() : null,
                'codigo' => $evento->codigo ?: $item['codigo'],
            ])->save();

      

            $multimedia = Multimedia::on('tenant_casa_angel')->firstOrCreate(
                [
                    'url' => $item['url'],
                    'preview_url' => $item['preview_url'] ?? $item['url'],
                   
                ],
                [
                    'mime_type' => 'image/jpeg',
                    'alt' => sprintf(
                        'Evento %s del %s foto %s',
                        $evento->nombre,
                        $evento->fecha_evento?->toDateString() ?? now()->toDateString(),
                        basename($item['url']),
                    ),
                    'aspect_ratio' =>isset($item['orientacion']) ? $this->orientationClassToAspectRatio($item['orientacion']) : null,
                    
                ],
            );

            $multimedia->forceFill([
                'alt' => $multimedia->alt ?: sprintf(
                    'Evento %s del %s foto %s',
                    $evento->nombre,
                    $evento->fecha_evento?->toDateString() ?? now()->toDateString(),
                    basename($item['url']),
                ),
                'preview_url' => $multimedia->preview_url ?: $item['preview_url'] ?? $item['url'],
                'aspect_ratio' => $multimedia->aspect_ratio ?: (isset($item['orientacion']) ? $this->orientationClassToAspectRatio($item['orientacion']) : null),
            ])->save();

            $evento->multimedias()->syncWithoutDetaching([$multimedia->id => ['cantidad' => $item['cantidad'] ?? 0]]);
        }
    }

    private function galleryItems(): array
    {
        $json = file_get_contents(resource_path('utils/galeria.json'));

        return json_decode($json ?: '[]', true, 512, JSON_THROW_ON_ERROR);
    }
    private function dateParse($dateString): string
    {    
        return Carbon::parse($dateString)? Carbon::parse($dateString)->format('Y-m-d') : null;
    }
    private function addFortyDays($dateString): string
    {
        return Carbon::parse($dateString)? Carbon::parse($dateString)->addDays(40)->format('Y-m-d') : null;
    }

    private function orientationClassToAspectRatio($orientationClass): string
    { 
   
        return $orientationClass === 'col-sm-4' ? '2:1' : '1:2';
    }


}
