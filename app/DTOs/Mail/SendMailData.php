<?php

namespace App\DTOs\Mail;

class SendMailData
{
    /**
     * @param  array<int, string>  $cc
     * @param  array<int, string>  $bcc
     * @param  array<string, mixed>  $data
     * @param  array<int, array{path: string, name?: string}>  $attachments
     */
    public function __construct(
        public readonly string $to,
        public readonly string $template,
        public readonly string $subject,
        public readonly array $data = [],
        public readonly ?int $conferenceId = null,
        public readonly ?int $userId = null,
        public readonly array $cc = [],
        public readonly array $bcc = [],
        public readonly array $attachments = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            to: (string) $data['to'],
            template: (string) $data['template'],
            subject: (string) $data['subject'],
            data: $data['data'] ?? [],
            conferenceId: $data['conference_id'] ?? null,
            userId: $data['user_id'] ?? null,
            cc: $data['cc'] ?? [],
            bcc: $data['bcc'] ?? [],
            attachments: $data['attachments'] ?? [],
        );
    }
}
