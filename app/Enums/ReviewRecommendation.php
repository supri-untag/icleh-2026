<?php

namespace App\Enums;

enum ReviewRecommendation: string
{
    case Accept = 'accept';
    case MinorRevision = 'minor_revision';
    case MajorRevision = 'major_revision';
    case Reject = 'reject';

    public function label(): string
    {
        return match ($this) {
            self::Accept => 'Accept',
            self::MinorRevision => 'Minor Revision',
            self::MajorRevision => 'Major Revision',
            self::Reject => 'Reject',
        };
    }
}
