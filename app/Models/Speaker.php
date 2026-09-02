<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'conference_id',
    'type',
    'name',
    'title',
    'affiliation',
    'country',
    'biography',
    'topic_title',
    'photo',
    'attendance_mode',
    'display_order',
    'active',
])]
class Speaker extends Model
{
    use HasFactory, HasUuid;

    public function conference(): BelongsTo
    {
        return $this->belongsTo(Conference::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function photoUrl(): ?string
    {
        if (blank($this->photo)) {
            return null;
        }

        $photo = (string) $this->photo;

        if (Str::startsWith($photo, ['http://', 'https://', '/'])) {
            return $photo;
        }

        if (Str::startsWith($photo, 'storage/')) {
            return asset($photo);
        }

        if (Str::startsWith($photo, ['images/', 'assets/'])) {
            return asset($photo);
        }

        return Storage::disk('public')->url($photo);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }
}
