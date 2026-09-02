<?php

namespace App\Enums;

enum PublicationStatus: string
{
    case NotSelected = 'not_selected';
    case Candidate = 'candidate';
    case Selected = 'selected';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::NotSelected => 'Not Selected',
            self::Candidate => 'Candidate',
            self::Selected => 'Selected',
            self::Published => 'Published',
        };
    }
}
