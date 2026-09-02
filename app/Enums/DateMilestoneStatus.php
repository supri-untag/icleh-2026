<?php

namespace App\Enums;

enum DateMilestoneStatus: string
{
    case Upcoming = 'upcoming';
    case Open = 'open';
    case Closed = 'closed';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Upcoming => 'Upcoming',
            self::Open => 'Open',
            self::Closed => 'Closed',
            self::Completed => 'Completed',
        };
    }
}
