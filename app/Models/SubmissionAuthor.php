<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'submission_id',
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

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'corresponding_author' => 'boolean',
            'presenter' => 'boolean',
        ];
    }
}
