<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Categoria extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'nivel',
    ];

    public function categoriaable(): MorphTo
    {
        return $this->morphTo();
    }
}
