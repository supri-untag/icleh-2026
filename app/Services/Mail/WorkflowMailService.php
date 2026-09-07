<?php

namespace App\Services\Mail;

use App\DTOs\Mail\SendMailData;
use App\Models\Conference;
use App\Models\Submission;
use App\Models\User;

class WorkflowMailService
{
    public function __construct(private MailService $mailService) {}

    /** @param array<string, mixed> $data */
    public function user(User $user, string $code, string $subject, array $data = [], ?Conference $conference = null): void
    {
        $this->mailService->queue(SendMailData::fromArray([
            'to' => $user->email,
            'template' => $code,
            'subject' => $subject.' - ICLEH 2026',
            'conference_id' => $conference?->id,
            'user_id' => $user->id,
            'data' => array_merge([
                'participant_name' => $user->name,
                'conference_name' => $conference?->name ?? 'ICLEH 2026',
                'activity' => $subject,
                'action_url' => route('participant.dashboard'),
            ], $data),
        ]));
    }

    /** @param array<string, mixed> $data */
    public function authors(Submission $submission, string $code, string $subject, array $data = []): void
    {
        $submission->loadMissing(['user', 'conference', 'authors.user']);
        $users = collect([$submission->user])->merge($submission->authors->pluck('user')->filter())->unique('email');

        foreach ($users as $user) {
            $this->user($user, $code, $subject, array_merge([
                'submission_title' => $submission->title,
                'submission_code' => $submission->submission_code,
                'status' => $submission->status->label(),
            ], $data), $submission->conference);
        }
    }

    /**
     * @param  array<int, string>  $roles
     * @param  array<string, mixed>  $data
     */
    public function committee(Conference $conference, array $roles, string $code, string $subject, array $data = []): void
    {
        $users = User::query()->whereHas('roles', fn ($query) => $query->whereIn('name', $roles))->get();
        foreach ($users->unique('email') as $user) {
            $this->user($user, $code, $subject, $data, $conference);
        }
    }
}
