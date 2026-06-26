<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Papeleta extends Model
{
    protected $table = 'papeletas';

    protected $fillable = [
        'unidad_id',
        'conductor_id',
        'nro_papeleta',
        'monto',
        'fecha_emision',
        'infraccion',
        'estado_pago',
        'fecha_pago',
        'operativo_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'fecha_pago' => 'date',
            'monto' => 'float',
        ];
    }

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class);
    }

    public function conductor(): BelongsTo
    {
        return $this->belongsTo(Conductor::class);
    }

    public function operativo(): BelongsTo
    {
        return $this->belongsTo(Operativo::class);
    }
}
