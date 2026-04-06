<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Taller extends Model
{
    protected $connection = 'tenant_biotek';

    protected $table = 'talleres';

    protected $fillable = [
        'nombre',
        'fecha',
        'duracion',
    ];

    public function estudiantes(): BelongsToMany
    {
        return $this->belongsToMany(BiotekEstudiante::class, 'estudiantes_talleres', 'taller_id', 'estudiante_id')
            ->withPivot(['pago', 'abono', 'debe', 'saldo_total'])
            ->withTimestamps();
    }
}