<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Direccion extends Model
{
    //
    protected $fillable = [
        'direccionable_id',
        'direccionable_type',
        'nomenclatura',
        'codigo_postal',
 
    ];
    protected $table = 'direcciones';
}
