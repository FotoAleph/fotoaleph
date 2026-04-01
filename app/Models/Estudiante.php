<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    protected $connection = 'tenant_sport_bogota';

    protected $fillable = [
        'nombre',
        'categoria',
        'foto_url',
    ];

    protected $table = 'estudiantes';
}
