<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupervisorMeeting extends Model
{
    protected $table = 'supervisor_meetings';

    protected $fillable = [
        'fecha',
        'tema',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(SupervisorAttendance::class, 'meeting_id');
    }
}
