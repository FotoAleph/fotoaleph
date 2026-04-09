<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Evento extends Model
{
    protected $connection = 'tenant_casa_angel';

    protected $fillable = [

        'nombre',
        'descripcion',
        'fecha_evento',
        'ubicacion',
        'codigo',
  
    ];

    protected $casts = [
        'fecha_evento' => 'datetime',
    ];



    public function multimedias(): BelongsToMany
    {
        return $this->belongsToMany(Multimedia::class, 'evento_multimedia')
            ->withTimestamps();
    }

    public function primaryMedia(): ?Multimedia
    {
        return $this->multimedias()->orderBy('cantidad', 'desc')->first();
    }
}
