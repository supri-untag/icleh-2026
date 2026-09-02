<?php

namespace App\Models;

use App\Enums\CertificateType;
use App\Enums\DocumentStatus;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'conference_id',
    'registration_id',
    'submission_id',
    'document_template_id',
    'type',
    'recipient_name',
    'certificate_number',
    'verification_code',
    'pdf_path',
    'issued_date',
    'status',
])]
class Certificate extends Model
{
    use HasFactory, HasUuid;

    public function conference(): BelongsTo
    {
        return $this->belongsTo(Conference::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

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
            'type' => CertificateType::class,
            'issued_date' => 'date',
            'status' => DocumentStatus::class,
        ];
    }
}
