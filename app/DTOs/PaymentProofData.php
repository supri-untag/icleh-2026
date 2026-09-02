<?php

namespace App\DTOs;

use Illuminate\Http\UploadedFile;

class PaymentProofData
{
    public function __construct(
        public readonly UploadedFile $proofFile,
        public readonly ?string $paidAt,
        public readonly ?string $notes,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            proofFile: $data['proof_file'],
            paidAt: $data['paid_at'] ?? null,
            notes: $data['notes'] ?? null,
        );
    }
}
