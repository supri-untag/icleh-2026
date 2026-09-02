<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'name',
    'slug',
    'edition',
    'theme',
    'description',
    'start_date',
    'end_date',
    'timezone',
    'mode',
    'venue_name',
    'location',
    'registration_requires_verified_payment',
    'meta_title',
    'meta_description',
    'active',
])]
class Conference extends Model
{
    use HasFactory, HasUuid;

    public function dates(): HasMany
    {
        return $this->hasMany(ConferenceDate::class);
    }

    public function topics(): HasMany
    {
        return $this->hasMany(ConferenceTopic::class);
    }

    public function registrationFees(): HasMany
    {
        return $this->hasMany(RegistrationFee::class);
    }

    public function speakers(): HasMany
    {
        return $this->hasMany(Speaker::class);
    }

    public function venue(): HasOne
    {
        return $this->hasOne(Venue::class)->where('active', true);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function days(): HasMany
    {
        return $this->hasMany(ConferenceDay::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class);
    }

    public function partners(): HasMany
    {
        return $this->hasMany(Partner::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'registration_requires_verified_payment' => 'boolean',
            'active' => 'boolean',
        ];
    }
}
