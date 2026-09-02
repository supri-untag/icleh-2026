<?php

namespace App\Jobs;

use App\DTOs\Mail\SendMailData;
use App\Models\MailLog;
use App\Services\Mail\MailLogService;
use App\Services\Mail\MailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SendConferenceMailJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    /**
     * @var array<int, int>
     */
    public array $backoff = [10, 60, 300];

    public function __construct(
        public SendMailData $data,
        public int $mailLogId,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(MailService $mailService): void
    {
        $mailLog = MailLog::query()->findOrFail($this->mailLogId);

        $mailService->send($this->data, $mailLog);
    }

    public function failed(?Throwable $exception): void
    {
        $mailLog = MailLog::query()->find($this->mailLogId);

        if ($mailLog) {
            app(MailLogService::class)->markFailed($mailLog, $exception ?? 'Unknown mail job failure.');
        }
    }
}
