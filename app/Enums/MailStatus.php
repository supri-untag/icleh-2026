<?php

namespace App\Enums;

enum MailStatus: string
{
    case Queued = 'queued';
    case Processing = 'processing';
    case Sent = 'sent';
    case Failed = 'failed';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
