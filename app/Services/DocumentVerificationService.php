<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\LoaDocument;

class DocumentVerificationService
{
    public function loa(string $code): ?LoaDocument
    {
        return LoaDocument::query()
            ->with(['submission.conference', 'submission.user'])
            ->where('verification_code', $code)
            ->first();
    }

    public function certificate(string $code): ?Certificate
    {
        return Certificate::query()
            ->with(['conference', 'registration.user', 'submission'])
            ->where('verification_code', $code)
            ->first();
    }
}
