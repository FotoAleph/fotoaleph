<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Evento extends Model
{
    protected $connection = 'tenant_casa_angel';

    protected $fillable = [
        'nombre',
        'descripcion',
        'fecha_evento',
        'ubicacion',
        'codigo',
        'publicar_en_vitrina',
    ];

    protected $casts = [
        'fecha_evento' => 'datetime',
        'publicar_en_vitrina' => 'boolean',
    ];

    public function multimedias(): MorphToMany
    {
        return $this->morphToMany(
            Multimedia::class,
            'multimediable',
            'multimediable',
            'multimediable_id',
            'multimedia_id'
        )->withTimestamps();
    }

    public function primaryMedia(): ?Multimedia
    {
        return $this->multimedias->first();
    }
}
