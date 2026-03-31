<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JymCategoria extends Model
{
    protected $connection = 'tenant_jym';

    protected $table = 'categorias';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function proyectos(): HasMany
    {
        return $this->hasMany(Proyecto::class, 'categoria_id');
    }
}
