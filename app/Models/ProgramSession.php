<?php

namespace App\Models;

use App\Enums\ProgramSessionType;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['conference_day_id', 'name', 'type', 'start_time', 'end_time', 'display_order'])]
class ProgramSession extends Model
{
    use HasFactory, HasUuid;

    public function day(): BelongsTo
    {
        return $this->belongsTo(ConferenceDay::class, 'conference_day_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ProgramSchedule::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ProgramSessionType::class,
        ];
    }
}
