<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Grupo extends Model
{

    protected $connection = 'tenant_jym';

    protected $fillable = [
        'nombre',
        'descripcion',
        'nivel',
    ];
    
    public function proyectos()
    {
        return $this->hasMany(Proyecto::class, 'grupo_id');
    }

    protected $casts = [
        'nivel' => 'integer',
    ];
}
