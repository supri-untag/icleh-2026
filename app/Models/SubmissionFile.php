<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'submission_id',
    'uploaded_by',
    'type',
    'version',
    'original_filename',
    'storage_path',
    'mime',
    'size',
    'final',
    'uploaded_at',
])]
class SubmissionFile extends Model
{
    use HasFactory, HasUuid;

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'size' => 'integer',
            'final' => 'boolean',
            'uploaded_at' => 'datetime',
        ];
    }
}
