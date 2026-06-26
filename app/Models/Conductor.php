<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conductor extends Model
{
    protected $table = 'conductores';

    protected $fillable = [
        'nombre',
        'dni',
        'licencia',
        'fecha_licencia_vencimiento',
        'telefono',
        'estado',
        'fecha_citacion',
        'motivo_citacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_licencia_vencimiento' => 'date',
            'fecha_citacion' => 'date',
        ];
    }

    public function unidad(): HasOne
    {
        return $this->hasOne(Unidad::class);
    }
}
