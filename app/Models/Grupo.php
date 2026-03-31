<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Grupo extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'nivel',
    ];

    public function grupoable(): MorphTo
    {
        return $this->morphTo();
    }
}
