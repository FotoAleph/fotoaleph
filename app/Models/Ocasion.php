<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ocasion extends Model
{
    protected $connection = 'tenant_casa_angel';
    protected $fillable = [
        'nombre',
        'descripcion',
        'nivel',
    ];


    public function eventos()
    {
        return $this->hasMany(Evento::class);
    }

    protected $casts = [
        'nivel' => 'integer',
    ];
    protected $table = 'ocasiones';

}
