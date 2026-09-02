<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Participant = 'participant';
    case Presenter = 'presenter';
    case Reviewer = 'reviewer';
    case ScientificCommittee = 'scientific_committee';
    case Finance = 'finance';
    case EventCommittee = 'event_committee';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin => 'Admin',
            self::Participant => 'Participant',
            self::Presenter => 'Presenter / Author',
            self::Reviewer => 'Reviewer',
            self::ScientificCommittee => 'Scientific Committee / Editor',
            self::Finance => 'Finance',
            self::EventCommittee => 'Event / Program Committee',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function adminValues(): array
    {
        return [
            self::SuperAdmin->value,
            self::Admin->value,
            self::ScientificCommittee->value,
            self::Finance->value,
            self::EventCommittee->value,
        ];
    }
}
