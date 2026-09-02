<?php

namespace App\Models;

use App\Enums\AttendanceMethod;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'registration_id',
    'program_schedule_id',
    'recorded_by',
    'attendance_date',
    'checked_in_at',
    'checked_out_at',
    'method',
])]
class Attendance extends Model
{
    use HasFactory, HasUuid;

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ProgramSchedule::class, 'program_schedule_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'checked_in_at' => 'datetime',
            'checked_out_at' => 'datetime',
            'method' => AttendanceMethod::class,
        ];
    }
}
