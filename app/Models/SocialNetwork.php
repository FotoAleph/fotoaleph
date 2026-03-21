<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialNetwork extends Model
{
    //
    protected $fillable = [
        'socialable_id',
        'socialable_type',
        'name',
        'url',
        'icon',
    ];

    protected $table = 'redes_sociales';
}
