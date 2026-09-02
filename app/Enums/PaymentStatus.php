<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Waiting = 'waiting';
    case Submitted = 'submitted';
    case Verified = 'verified';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Waiting => 'Waiting',
            self::Submitted => 'Submitted',
            self::Verified => 'Verified',
            self::Rejected => 'Rejected',
        };
    }
}
