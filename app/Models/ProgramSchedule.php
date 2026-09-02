<?php

namespace App\Models;

use App\Enums\ProgramSessionType;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'conference_day_id',
    'program_session_id',
    'chamber_id',
    'speaker_id',
    'submission_id',
    'start_time',
    'end_time',
    'type',
    'title',
    'moderator',
    'operator',
    'presentation_order',
    'notes',
    'published',
])]
class ProgramSchedule extends Model
{
    use HasFactory, HasUuid;

    public function day(): BelongsTo
    {
        return $this->belongsTo(ConferenceDay::class, 'conference_day_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ProgramSession::class, 'program_session_id');
    }

    public function chamber(): BelongsTo
    {
        return $this->belongsTo(Chamber::class);
    }

    public function speaker(): BelongsTo
    {
        return $this->belongsTo(Speaker::class);
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ProgramSessionType::class,
            'published' => 'boolean',
            'presentation_order' => 'integer',
        ];
    }
}
