<?php

namespace App\Models;

use App\Enums\ReviewRecommendation;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'review_assignment_id',
    'comments_for_author',
    'confidential_comments',
    'recommendation',
    'attachment',
    'submitted_at',
])]
class Review extends Model
{
    use HasFactory, HasUuid;

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(ReviewAssignment::class, 'review_assignment_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(ReviewScore::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'recommendation' => ReviewRecommendation::class,
            'submitted_at' => 'datetime',
        ];
    }
}
