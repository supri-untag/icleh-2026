<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['page_id', 'key', 'title', 'body', 'data', 'display_order', 'published'])]
class PageSection extends Model
{
    use HasFactory, HasUuid;

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
            'published' => 'boolean',
        ];
    }
}
