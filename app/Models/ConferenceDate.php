<?php

namespace App\Models;

use App\Enums\DateMilestoneStatus;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['conference_id', 'name', 'starts_at', 'ends_at', 'status', 'display_order', 'visible'])]
class ConferenceDate extends Model
{
    use HasFactory, HasUuid;

    public function conference(): BelongsTo
    {
        return $this->belongsTo(Conference::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'status' => DateMilestoneStatus::class,
            'visible' => 'boolean',
        ];
    }
}
