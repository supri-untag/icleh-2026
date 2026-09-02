<?php

namespace App\Services\Mail;

use App\DTOs\Mail\SendMailData;
use App\Enums\MailStatus;
use App\Models\MailLog;
use Throwable;

class MailLogService
{
    public function createQueued(SendMailData $data): MailLog
    {
        return MailLog::query()->create([
            'conference_id' => $data->conferenceId,
            'user_id' => $data->userId,
            'recipient' => $data->to,
            'cc' => $data->cc === [] ? null : implode(',', $data->cc),
            'bcc' => $data->bcc === [] ? null : implode(',', $data->bcc),
            'template_code' => $data->template,
            'subject' => $data->subject,
            'status' => MailStatus::Queued,
            'queued_at' => now(),
        ]);
    }

    public function markProcessing(MailLog $mailLog): void
    {
        $mailLog->update(['status' => MailStatus::Processing]);
    }

    public function markSent(MailLog $mailLog): void
    {
        $mailLog->update([
            'status' => MailStatus::Sent,
            'sent_at' => now(),
            'failed_at' => null,
            'error_message' => null,
        ]);
    }

    public function markFailed(MailLog $mailLog, Throwable|string $error): void
    {
        $message = $error instanceof Throwable ? $error->getMessage() : $error;

        $mailLog->update([
            'status' => MailStatus::Failed,
            'failed_at' => now(),
            'error_message' => $message,
        ]);
    }
}
