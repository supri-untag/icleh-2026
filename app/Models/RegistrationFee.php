<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'conference_id',
    'name',
    'description',
    'participant_type',
    'attendance_mode',
    'amount',
    'currency',
    'active',
    'quota',
    'registration_start',
    'registration_end',
])]
class RegistrationFee extends Model
{
    use HasFactory, HasUuid;

    public function conference(): BelongsTo
    {
        return $this->belongsTo(Conference::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function formattedAmount(): string
    {
        return 'Rp'.number_format((int) $this->amount, 0, ',', '.');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'active' => 'boolean',
            'registration_start' => 'date',
            'registration_end' => 'date',
        ];
    }
}
