<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cotizacion extends Model
{
    protected $fillable = [
        'user_id',
        'product_name',
        'description',
        'quantity',
        'estimated_price',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
