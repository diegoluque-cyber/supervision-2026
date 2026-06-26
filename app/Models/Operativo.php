<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Operativo extends Model
{
    protected $table = 'operativos';

    protected $fillable = [
        'nombre',
        'fecha',
        'descripcion',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
        ];
    }

    public function faltas(): HasMany
    {
        return $this->hasMany(Falta::class, 'operativo_id');
    }

    public function papeletas(): HasMany
    {
        return $this->hasMany(Papeleta::class, 'operativo_id');
    }

    public function intervenciones(): HasMany
    {
        return $this->hasMany(OperativoIntervencion::class, 'operativo_id');
    }
}
