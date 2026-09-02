<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['conference_id', 'name', 'address', 'description', 'map_url', 'photo', 'active'])]
class Venue extends Model
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
            'active' => 'boolean',
        ];
    }
}
