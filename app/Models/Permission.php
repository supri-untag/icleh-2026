<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'label'])]
class Permission extends Model
{
    use HasFactory, HasUuid;

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }
}
