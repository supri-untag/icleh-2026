<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['submission_id', 'changed_by', 'from_status', 'to_status', 'notes'])]
class SubmissionStatusHistory extends Model
{
    use HasFactory, HasUuid;

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
