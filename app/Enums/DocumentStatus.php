<?php

namespace App\Enums;

enum DocumentStatus: string
{
    case Draft = 'draft';
    case Issued = 'issued';
    case Revoked = 'revoked';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Issued => 'Issued',
            self::Revoked => 'Revoked',
        };
    }
}
