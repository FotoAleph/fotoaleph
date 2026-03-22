<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialNetworkType extends Model
{
    protected $fillable = [
        'name',
        'icon',
        'base_url',
    ];

    public function socialNetworks()
    {
        return $this->hasMany(SocialNetwork::class);
    }
}
