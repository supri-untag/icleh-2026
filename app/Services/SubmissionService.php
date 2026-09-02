<?php

namespace App\Services;

use App\DTOs\Mail\SendMailData;
use App\DTOs\SubmissionData;
use App\Enums\RegistrationStatus;
use App\Enums\SubmissionStatus;
use App\Models\AuditLog;
use App\Models\LoaDocument;
use App\Models\Registration;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\User;
use App\Services\Mail\MailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SubmissionService
{
    public function __construct(
        private ConferenceContext $conferenceContext,
        private MailService $mailService,
    ) {}

    public function create(User $user, SubmissionData $data): Submission
    {
        $conference = $this->conferenceContext->current();
        $registration = Registration::query()
            ->whereBelongsTo($conference)
            ->whereBelongsTo($user)
            ->with(['payment', 'conference'])
            ->first();

        if (! $registration) {
            throw ValidationException::withMessages([
                'registration' => 'Please complete conference registration before submitting an abstract.',
            ]);
        }

        if (! $registration->isPresenter()) {
            throw ValidationException::withMessages([
                'participant_type' => 'Only presenters can submit abstracts.',
            ]);
        }

        if ($conference->registration_requires_verified_payment && ! in_array($registration->status, [
            RegistrationStatus::PaymentVerified,
            RegistrationStatus::Confirmed,
            RegistrationStatus::Attended,
            RegistrationStatus::Completed,
        ], true)) {
            throw ValidationException::withMessages([
                'payment' => 'Payment must be verified before abstract submission.',
            ]);
        }

        return DB::transaction(function () use ($user, $conference, $registration, $data): Submission {
            $submission = Submission::query()->create([
                'conference_id' => $conference->id,
                'user_id' => $user->id,
                'registration_id' => $registration->id,
                'conference_topic_id' => $data->conferenceTopicId,
                'submission_code' => $this->newCode('ICLEH-ABS', Submission::class, 'submission_code'),
                'title' => $data->title,
                'abstract_text' => $data->abstractText,
                'keywords' => $data->keywords,
                'corresponding_author' => $data->correspondingAuthor,
                'affiliations' => $data->affiliations,
                'country' => $data->country,
                'notes' => $data->notes,
                'status' => SubmissionStatus::AbstractSubmitted,
                'submitted_at' => now(),
            ]);

            if ($data->abstractFile) {
                $path = $data->abstractFile->store('submissions/'.$submission->uuid.'/abstracts');
                $submission->update(['abstract_file' => $path]);

                SubmissionFile::query()->create([
                    'submission_id' => $submission->id,
                    'uploaded_by' => $user->id,
                    'type' => 'abstract',
                    'version' => 1,
                    'original_filename' => $data->abstractFile->getClientOriginalName(),
                    'storage_path' => $path,
                    'mime' => $data->abstractFile->getClientMimeType(),
                    'size' => $data->abstractFile->getSize(),
                    'uploaded_at' => now(),
                ]);
            }

            $this->syncAuthors($submission, $user, $data->authors);
            $this->recordStatusHistory($submission, null, SubmissionStatus::AbstractSubmitted, $user, 'Abstract submitted by presenter.');

            AuditLog::query()->create([
                'actor_id' => $user->id,
                'action' => 'submission.created',
                'subject_type' => Submission::class,
                'subject_id' => (string) $submission->id,
                'after' => $submission->only(['submission_code', 'title', 'status']),
            ]);

            $this->mailService->queue(SendMailData::fromArray([
                'to' => $user->email,
                'template' => 'abstract_submitted',
                'subject' => 'Abstract Submitted - ICLEH 2026',
                'conference_id' => $conference->id,
                'user_id' => $user->id,
                'data' => [
                    'participant_name' => $user->name,
                    'conference_name' => $conference->name,
                    'submission_title' => $submission->title,
                    'submission_code' => $submission->submission_code,
                ],
            ]));

            return $submission->refresh()->load(['topic', 'authors', 'files', 'histories']);
        });
    }

    public function changeStatus(Submission $submission, SubmissionStatus $status, User $actor, ?string $notes = null): Submission
    {
        return DB::transaction(function () use ($submission, $status, $actor, $notes): Submission {
            $submission->loadMissing(['conference', 'user', 'loaDocument']);
            $before = $submission->status;

            $submission->update([
                'status' => $status,
                'accepted_at' => $status === SubmissionStatus::AbstractAccepted ? now() : $submission->accepted_at,
                'final_decision_at' => in_array($status, [
                    SubmissionStatus::AbstractAccepted,
                    SubmissionStatus::AbstractRejected,
                ], true) ? now() : $submission->final_decision_at,
            ]);

            $this->recordStatusHistory($submission, $before, $status, $actor, $notes);

            if ($status === SubmissionStatus::AbstractAccepted) {
                $this->issueLoa($submission, $actor);

                $this->mailService->queue(SendMailData::fromArray([
                    'to' => $submission->user->email,
                    'template' => 'abstract_accepted',
                    'subject' => 'Abstract Accepted - ICLEH 2026',
                    'conference_id' => $submission->conference_id,
                    'user_id' => $submission->user_id,
                    'data' => [
                        'participant_name' => $submission->user->name,
                        'conference_name' => $submission->conference->name,
                        'submission_title' => $submission->title,
                    ],
                ]));
            }

            if ($status === SubmissionStatus::AbstractRejected) {
                $this->mailService->queue(SendMailData::fromArray([
                    'to' => $submission->user->email,
                    'template' => 'abstract_rejected',
                    'subject' => 'Abstract Decision - ICLEH 2026',
                    'conference_id' => $submission->conference_id,
                    'user_id' => $submission->user_id,
                    'data' => [
                        'participant_name' => $submission->user->name,
                        'conference_name' => $submission->conference->name,
                        'submission_title' => $submission->title,
                    ],
                ]));
            }

            AuditLog::query()->create([
                'actor_id' => $actor->id,
                'action' => 'submission.status_changed',
                'subject_type' => Submission::class,
                'subject_id' => (string) $submission->id,
                'before' => ['status' => $before?->value],
                'after' => ['status' => $status->value],
            ]);

            return $submission->refresh()->load(['topic', 'authors', 'histories', 'loaDocument']);
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $authors
     */
    private function syncAuthors(Submission $submission, User $user, array $authors): void
    {
        if ($authors === []) {
            $authors = [[
                'name' => $user->name,
                'email' => $user->email,
                'affiliation' => $user->institution,
                'country' => $user->country,
                'corresponding_author' => true,
                'presenter' => true,
            ]];
        }

        foreach (array_values($authors) as $index => $author) {
            $submission->authors()->create([
                'name' => $author['name'],
                'email' => $author['email'] ?? null,
                'affiliation' => $author['affiliation'] ?? null,
                'country' => $author['country'] ?? null,
                'corresponding_author' => (bool) ($author['corresponding_author'] ?? false),
                'presenter' => (bool) ($author['presenter'] ?? false),
                'order' => $index + 1,
            ]);
        }
    }

    private function recordStatusHistory(
        Submission $submission,
        ?SubmissionStatus $from,
        SubmissionStatus $to,
        ?User $actor,
        ?string $notes,
    ): void {
        $submission->histories()->create([
            'changed_by' => $actor?->id,
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'notes' => $notes,
        ]);
    }

    private function issueLoa(Submission $submission, User $actor): LoaDocument
    {
        $loa = LoaDocument::query()->firstOrCreate(
            ['submission_id' => $submission->id],
            [
                'loa_number' => $this->newCode('ICLEH-LOA', LoaDocument::class, 'loa_number'),
                'verification_code' => $this->newCode('LOA', LoaDocument::class, 'verification_code'),
                'issued_date' => now()->toDateString(),
                'signer_name' => 'Dean, Faculty of Law',
                'signer_title' => 'Universitas 17 Agustus 1945 Semarang',
                'status' => 'issued',
            ],
        );

        if ($loa->wasRecentlyCreated) {
            AuditLog::query()->create([
                'actor_id' => $actor->id,
                'action' => 'loa.issued',
                'subject_type' => LoaDocument::class,
                'subject_id' => (string) $loa->id,
                'after' => $loa->only(['loa_number', 'verification_code', 'issued_date']),
            ]);

            $this->mailService->queue(SendMailData::fromArray([
                'to' => $submission->user->email,
                'template' => 'loa_issued',
                'subject' => 'Letter of Acceptance Issued - ICLEH 2026',
                'conference_id' => $submission->conference_id,
                'user_id' => $submission->user_id,
                'data' => [
                    'participant_name' => $submission->user->name,
                    'conference_name' => $submission->conference->name,
                    'submission_title' => $submission->title,
                    'loa_number' => $loa->loa_number,
                ],
            ]));
        }

        return $loa;
    }

    private function newCode(string $prefix, string $modelClass, string $column): string
    {
        do {
            $code = $prefix.'-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
        } while ($modelClass::query()->where($column, $code)->exists());

        return $code;
    }
}
