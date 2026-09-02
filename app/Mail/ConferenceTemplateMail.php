<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConferenceTemplateMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<int, array{path: string, name?: string}>  $attachments
     */
    public function __construct(
        private string $subject,
        private string $html,
        private string $text,
        private array $attachments = [],
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            text: 'mail.plain',
            htmlString: $this->html,
            with: ['text' => $this->text],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return collect($this->attachments)
            ->map(function (array $attachment): Attachment {
                $mailAttachment = Attachment::fromStorageDisk('local', $attachment['path']);

                if (isset($attachment['name'])) {
                    $mailAttachment->as($attachment['name']);
                }

                return $mailAttachment;
            })
            ->all();
    }
}
