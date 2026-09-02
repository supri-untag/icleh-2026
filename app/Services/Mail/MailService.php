<?php

namespace App\Services\Mail;

use App\DTOs\Mail\SendMailData;
use App\Jobs\SendConferenceMailJob;
use App\Mail\ConferenceTemplateMail;
use App\Models\MailLog;
use Illuminate\Support\Facades\Mail;

class MailService
{
    public function __construct(
        private MailTemplateService $templateService,
        private MailLogService $mailLogService,
    ) {}

    public function queue(SendMailData $data): MailLog
    {
        $mailLog = $this->mailLogService->createQueued($data);

        SendConferenceMailJob::dispatch($data, $mailLog->id)->afterCommit();

        return $mailLog;
    }

    public function send(SendMailData $data, MailLog $mailLog): void
    {
        $this->mailLogService->markProcessing($mailLog);

        $content = $this->templateService->render(
            code: $data->template,
            subject: $data->subject,
            data: $data->data,
            conferenceId: $data->conferenceId,
        );

        $pendingMail = Mail::to($data->to);

        if ($data->cc !== []) {
            $pendingMail->cc($data->cc);
        }

        if ($data->bcc !== []) {
            $pendingMail->bcc($data->bcc);
        }

        $pendingMail->send(new ConferenceTemplateMail(
            subject: $content['subject'],
            html: $content['html'],
            text: $content['text'],
            attachments: $data->attachments,
        ));

        $this->mailLogService->markSent($mailLog);
    }
}
