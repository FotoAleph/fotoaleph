<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Evento extends Model
{
    protected $connection = 'tenant_casa_angel';

    protected $fillable = [
        'ocasion_id',
        'tematica_id',
        'nombre',
        'descripcion',
        'fecha_evento',
        'ubicacion',
    ];

    protected $casts = [
        'fecha_evento' => 'datetime',
    ];

    public function ocasion(): BelongsTo
    {
        return $this->belongsTo(Ocasion::class);
    }

    public function tematica(): BelongsTo
    {
        return $this->belongsTo(Tematica::class);
    }

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
}
