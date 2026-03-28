<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nivel extends Model
{
    protected $fillable = [
        'nivel',
        'nivelable_id',
        'nivelable_type',
    ];

    public function nivelable()
    {
        return $this->morphTo();
    }
}
