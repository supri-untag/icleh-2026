<?php

namespace App\Models;

use App\Enums\DocumentStatus;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'submission_id',
    'document_template_id',
    'loa_number',
    'verification_code',
    'pdf_path',
    'signer_name',
    'signer_title',
    'signature_image',
    'issued_date',
    'status',
])]
class LoaDocument extends Model
{
    use HasFactory, HasUuid;

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class, 'document_template_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'issued_date' => 'date',
            'status' => DocumentStatus::class,
        ];
    }
}
