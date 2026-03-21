<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Telefono extends Model
{
    //
    protected $fillable = [
        'telefonoable_id',
        'telefonoable_type',
        'number',
        'type',
    ];
    protected $table = 'telefonos';
}
