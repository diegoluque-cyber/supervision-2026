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
        'telefono',
        'estado',
    ];

    public function unidad(): HasOne
    {
        return $this->hasOne(Unidad::class);
    }
}
