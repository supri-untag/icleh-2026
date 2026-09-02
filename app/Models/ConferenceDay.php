<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['conference_id', 'date', 'label', 'display_order'])]
class ConferenceDay extends Model
{
    use HasFactory, HasUuid;

    public function conference(): BelongsTo
    {
        return $this->belongsTo(Conference::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ProgramSession::class)->orderBy('display_order');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ProgramSchedule::class)->orderBy('start_time');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }
}
