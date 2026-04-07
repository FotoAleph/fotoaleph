<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BiotekEstudiante extends Model
{
    protected $connection = 'tenant_biotek';

    protected $table = 'estudiantes';

    protected $fillable = [
        'nombres',
        'apellidos',
        'identificacion',
    ];

    public function multimedias(): BelongsToMany
    {
        return $this->belongsToMany(Multimedia::class, 'estudiante_multimedia')
            ->withTimestamps();
    }

    public function talleres(): BelongsToMany
    {
        return $this->belongsToMany(Taller::class, 'estudiantes_talleres', 'estudiante_id', 'taller_id')
            ->withPivot(['pago', 'abono', 'debe', 'saldo_total'])
            ->withTimestamps();
    }

    public function primaryMedia(): ?Multimedia
    {
        return $this->multimedias->first();
    }
}