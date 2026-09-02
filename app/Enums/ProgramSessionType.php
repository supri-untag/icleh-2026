<?php

namespace App\Enums;

enum ProgramSessionType: string
{
    case Registration = 'registration';
    case Opening = 'opening';
    case Keynote = 'keynote';
    case Plenary = 'plenary';
    case Break = 'break';
    case Parallel = 'parallel';
    case Award = 'award';
    case Closing = 'closing';

    public function label(): string
    {
        return match ($this) {
            self::Registration => 'Registration',
            self::Opening => 'Opening',
            self::Keynote => 'Keynote',
            self::Plenary => 'Plenary',
            self::Break => 'Break',
            self::Parallel => 'Parallel',
            self::Award => 'Award',
            self::Closing => 'Closing',
        };
    }
}
