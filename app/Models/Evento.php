<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Evento extends Model
{
    protected $connection = 'tenant_casa_angel';

    protected $fillable = [
        'ocasion_id',
        'tematica_id',
        'color_id',
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

    public function ocasion(): BelongsTo
    {
        return $this->belongsTo(Ocasion::class);
    }

    public function tematica(): BelongsTo
    {
        return $this->belongsTo(Tematica::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    public function multimedias(): BelongsToMany
    {
        return $this->belongsToMany(Multimedia::class, 'evento_multimedia')
            ->withTimestamps();
    }

    public function primaryMedia(): ?Multimedia
    {
        return $this->multimedias->first();
    }
}
