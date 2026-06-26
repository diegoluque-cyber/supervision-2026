<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupervisorAttendance extends Model
{
    protected $table = 'supervisor_attendance';

    protected $fillable = [
        'meeting_id',
        'unidad_id',
        'estado',
        'observacion',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(SupervisorMeeting::class, 'meeting_id');
    }

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class, 'unidad_id');
    }
}
