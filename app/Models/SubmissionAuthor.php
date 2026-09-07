<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'submission_id',
    'user_id',
    'registration_id',
    'participant',
    'name',
    'email',
    'affiliation',
    'country',
    'corresponding_author',
    'presenter',
    'order',
])]
class SubmissionAuthor extends Model
{
    use HasFactory, HasUuid;

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'corresponding_author' => 'boolean',
            'presenter' => 'boolean',
            'participant' => 'boolean',
        ];
    }
}
