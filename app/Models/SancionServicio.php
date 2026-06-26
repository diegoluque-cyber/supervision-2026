<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SancionServicio extends Model
{
    protected $table = 'sanciones_servicios';

    protected $fillable = [
        'conductor_id',
        'categoria_corporativo',
        'negociable_status',
        'corporativo_status',
        'motivo',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'negociable_status' => 'array',
            'corporativo_status' => 'array',
        ];
    }

    public function conductor(): BelongsTo
    {
        return $this->belongsTo(Conductor::class);
    }
}
