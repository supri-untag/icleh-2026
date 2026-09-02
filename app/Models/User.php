<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Models\Concerns\HasUuid;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'whatsapp', 'institution', 'country'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuid, Notifiable;

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function paymentsVerified(): HasMany
    {
        return $this->hasMany(Payment::class, 'verified_by');
    }

    public function assignRole(UserRole|string $role): void
    {
        $roleName = $role instanceof UserRole ? $role->value : $role;
        $roleModel = Role::query()->where('name', $roleName)->firstOrFail();

        $this->roles()->syncWithoutDetaching([$roleModel->id]);
    }

    public function hasRole(UserRole|string|array $roles): bool
    {
        $roleNames = collect((array) $roles)
            ->map(fn (UserRole|string $role): string => $role instanceof UserRole ? $role->value : $role)
            ->all();

        if ($this->relationLoaded('roles')) {
            return $this->roles->pluck('name')->intersect($roleNames)->isNotEmpty();
        }

        return $this->roles()->whereIn('name', $roleNames)->exists();
    }

    public function canAccessAdmin(): bool
    {
        return $this->hasRole(UserRole::adminValues());
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
