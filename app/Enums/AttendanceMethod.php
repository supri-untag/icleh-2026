<?php

namespace App\Enums;

enum AttendanceMethod: string
{
    case Qr = 'qr';
    case Manual = 'manual';
    case Import = 'import';

    public function label(): string
    {
        return match ($this) {
            self::Qr => 'QR Scan',
            self::Manual => 'Manual',
            self::Import => 'Import',
        };
    }
}
