<?php

namespace App\Enums;

enum CertificateType: string
{
    case Participant = 'participant';
    case Presenter = 'presenter';
    case Speaker = 'speaker';
    case KeynoteSpeaker = 'keynote_speaker';
    case Moderator = 'moderator';
    case Reviewer = 'reviewer';
    case Committee = 'committee';
    case BestPaper = 'best_paper';

    public function label(): string
    {
        return match ($this) {
            self::Participant => 'Participant',
            self::Presenter => 'Presenter',
            self::Speaker => 'Speaker',
            self::KeynoteSpeaker => 'Keynote Speaker',
            self::Moderator => 'Moderator',
            self::Reviewer => 'Reviewer',
            self::Committee => 'Committee',
            self::BestPaper => 'Best Paper',
        };
    }
}
