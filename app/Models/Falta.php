<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Falta extends Model
{
    public const TIPOS = [
        'observacion'                  => 'Observación',
        'falta_grave'                  => 'Falta Grave',
        'expulsado'                    => 'Expulsado',
        'tarifa_negociable'            => 'Tarifa Negociable',
        'libre'                        => 'Libre',
        'cancela_servicios_corporativos' => 'Cancela Servicios Corporativos',
        'bloqueo_aplicativo'           => 'Bloqueo de Aplicativo',
    ];

    public const TIPO_COLORS = [
        'observacion'                    => 'bg-yellow-100 text-yellow-800',
        'falta_grave'                    => 'bg-orange-100 text-orange-800',
        'expulsado'                      => 'bg-red-100 text-red-700',
        'tarifa_negociable'              => 'bg-blue-100 text-blue-700',
        'libre'                          => 'bg-green-100 text-green-700',
        'cancela_servicios_corporativos' => 'bg-purple-100 text-purple-700',
        'bloqueo_aplicativo'             => 'bg-rose-100 text-rose-800',
    ];

    protected $table = 'faltas';

    protected $fillable = [
        'unidad_id',
        'conductor_id',
        'tipo',
        'categoria',
        'descripcion',
        'fotos',
        'supervisor',
        'fecha_falta',
        'fecha_alarma',
        'alarma_leida',
        'operativo_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_falta' => 'datetime',
            'fecha_alarma' => 'date',
            'alarma_leida' => 'boolean',
            'fotos' => 'array',
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

    public function getTipoLabelAttribute(): string
    {
        return self::TIPOS[$this->tipo] ?? $this->tipo;
    }
}
