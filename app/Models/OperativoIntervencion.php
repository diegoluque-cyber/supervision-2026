<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperativoIntervencion extends Model
{
    protected $table = 'operativo_intervenciones';

    protected $fillable = [
        'operativo_id',
        'unidad_id',
        'conductor_id',
        'resultado', // 'sin_novedad', 'falta', 'papeleta'
        'observaciones',
    ];

    public function operativo(): BelongsTo
    {
        return $this->belongsTo(Operativo::class, 'operativo_id');
    }

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class, 'unidad_id');
    }

    public function conductor(): BelongsTo
    {
        return $this->belongsTo(Conductor::class, 'conductor_id');
    }
}
