<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialNetwork extends Model
{
    protected $fillable = [
        'socialable_id',
        'socialable_type',
        'social_network_type_id',
        'url',
    ];

    protected $table = 'redes_sociales';

    public function socialNetworkType()
    {
        return $this->belongsTo(SocialNetworkType::class);
    }
}
