<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JymGrupo extends Model
{
    protected $connection = 'tenant_jym';

    protected $table = 'grupos';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function proyectos(): HasMany
    {
        return $this->hasMany(Proyecto::class, 'grupo_id');
    }
}