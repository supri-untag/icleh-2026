<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'full_name',
    'whatsapp',
    'institution',
    'country',
    'participant_type',
    'attendance_mode',
    'status_proof_file',
])]
class Profile extends Model
{
    use HasFactory, HasUuid;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
