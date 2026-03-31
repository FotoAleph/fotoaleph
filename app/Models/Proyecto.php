<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'nivel',
    ];

    public function niveles()
    {
        return $this->morphMany(Nivel::class, 'nivelable');
    }

    public function multimedias()
    {
        return $this->morphMany(Multimedia::class, 'multimediaable');
    }
}
