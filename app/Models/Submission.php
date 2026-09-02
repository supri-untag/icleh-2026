<?php

namespace App\Models;

use App\Enums\SubmissionStatus;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'conference_id',
    'user_id',
    'registration_id',
    'conference_topic_id',
    'submission_code',
    'title',
    'abstract_text',
    'keywords',
    'corresponding_author',
    'affiliations',
    'country',
    'abstract_file',
    'notes',
    'status',
    'submitted_at',
    'accepted_at',
    'final_decision_at',
])]
class Submission extends Model
{
    use HasFactory, HasUuid;

    public function conference(): BelongsTo
    {
        return $this->belongsTo(Conference::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(ConferenceTopic::class, 'conference_topic_id');
    }

    public function authors(): HasMany
    {
        return $this->hasMany(SubmissionAuthor::class)->orderBy('order');
    }

    public function files(): HasMany
    {
        return $this->hasMany(SubmissionFile::class);
    }

    public function fullPapers(): HasMany
    {
        return $this->hasMany(SubmissionFile::class)->where('type', 'full_paper')->orderByDesc('version');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(SubmissionStatusHistory::class)->latest();
    }

    public function loaDocument(): HasOne
    {
        return $this->hasOne(LoaDocument::class);
    }

    public function reviewAssignments(): HasMany
    {
        return $this->hasMany(ReviewAssignment::class);
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
            'keywords' => 'array',
            'status' => SubmissionStatus::class,
            'submitted_at' => 'datetime',
            'accepted_at' => 'datetime',
            'final_decision_at' => 'datetime',
        ];
    }
}
