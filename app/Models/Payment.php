<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'registration_id',
    'payment_code',
    'method',
    'amount',
    'currency',
    'proof_file',
    'paid_at',
    'submitted_at',
    'verified_at',
    'verified_by',
    'status',
    'rejection_reason',
    'notes',
])]
class Payment extends Model
{
    use HasFactory, HasUuid;

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
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
            'paid_at' => 'datetime',
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
            'status' => PaymentStatus::class,
        ];
    }
}
