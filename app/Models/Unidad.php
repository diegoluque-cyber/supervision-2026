<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Unidad extends Model
{
    protected $table = 'unidades';

    public const CATEGORIAS = [
        'supervision' => 'Supervisión',
        'san_pablo' => 'San Pablo',
        'cerro_verde' => 'Cerro Verde',
        'mitsui' => 'Mitsui',
        'otros' => 'Otros',
    ];

    public const ESTADOS = [
        'activo' => 'Activo',
        'inactivo' => 'Inactivo',
        'mantenimiento' => 'Mantenimiento',
    ];

    protected $fillable = [
        'codigo',
        'placa',
        'categoria',
        'estado',
        'conductor_id',
        'marca',
        'modelo',
        'color',
        'anio',
    ];

    protected function casts(): array
    {
        return [
            // codigo ya es string, no necesita cast
        ];
    }

    public function conductor(): BelongsTo
    {
        return $this->belongsTo(Conductor::class);
    }

    public function faltas(): HasMany
    {
        return $this->hasMany(\App\Models\Falta::class);
    }

    public function getCategoriaLabelAttribute(): string
    {
        return self::CATEGORIAS[$this->categoria] ?? $this->categoria;
    }

    public function getEstadoLabelAttribute(): string
    {
        return self::ESTADOS[$this->estado] ?? $this->estado;
    }
}
