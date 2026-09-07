<?php

namespace Tests\Feature;

use App\Models\Conference;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\RegistrationFee;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\CoauthorAccountInvitation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SubmissionCoauthorTest extends TestCase
{
    public function test_multiple_coauthors_get_accounts_with_bills_only_for_participants(): void
    {
        [$user, $registration, $payload] = $this->presenterSubmission();
        $payload['authors'] = [
            ['name' => 'First Participant', 'email' => ' FIRST@example.test ', 'country' => 'Indonesia', 'participant' => '1'],
            ['name' => 'Second Participant', 'email' => 'second@example.test', 'country' => 'Malaysia', 'participant' => '1'],
            ['name' => 'Writer Only', 'email' => 'writer@example.test', 'country' => 'Indonesia'],
        ];

        $this->actingAs($user)->post(route('participant.submissions.store'), $payload)
            ->assertSessionHasNoErrors()->assertRedirect();

        $submission = Submission::query()->with('authors.registration.payment')->whereBelongsTo($user)->firstOrFail();
        $this->assertCount(4, $submission->authors);
        $this->assertSame($user->id, $submission->authors->first()->user_id);
        foreach (['first@example.test', 'second@example.test'] as $email) {
            $coauthor = User::query()->where('email', $email)->firstOrFail();
            $author = $submission->authors->firstWhere('email', $email);
            $this->assertSame($coauthor->id, $author->user_id);
            $this->assertTrue($author->participant);
            $this->assertSame($registration->registration_fee_id, $author->registration->registration_fee_id);
            $this->assertSame($registration->fee->amount, $author->registration->payment->amount);
            $this->assertSame('waiting', $author->registration->payment->status->value);
            $this->assertSame('presenter', $author->registration->participant_type);
            $this->assertSame('online', $author->registration->attendance_mode);
            Notification::assertSentTo($coauthor, CoauthorAccountInvitation::class);
        }
        $writer = User::query()->where('email', 'writer@example.test')->firstOrFail();
        $this->assertSame(0, $writer->registrations()->count());
        $this->assertNotNull($writer->profile);
        Notification::assertSentTo($writer, CoauthorAccountInvitation::class);
        $this->assertNull($submission->authors->firstWhere('email', 'writer@example.test')->registration_id);
    }

    public function test_existing_account_and_verified_bill_are_reused_without_changes(): void
    {
        [$user, $registration, $payload] = $this->presenterSubmission();
        $existing = User::factory()->create(['email' => 'existing@example.test']);
        $existingRegistration = Registration::factory()->for($existing)->create([
            'conference_id' => $registration->conference_id,
            'registration_fee_id' => $registration->registration_fee_id,
            'status' => 'confirmed',
        ]);
        $payment = Payment::factory()->for($existingRegistration)->create(['status' => 'verified', 'proof_file' => 'existing.jpg']);
        $userBefore = $existing->fresh()->getAttributes();
        $paymentBefore = $payment->fresh()->getAttributes();
        $registrationBefore = $existingRegistration->fresh()->getAttributes();
        $payload['authors'] = [['name' => 'Different Submitted Name', 'email' => $existing->email, 'country' => 'Malaysia', 'participant' => 1]];

        $this->actingAs($user)->post(route('participant.submissions.store'), $payload)->assertSessionHasNoErrors()->assertRedirect();
        $this->post(route('participant.submissions.store'), $payload)->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame(1, $existing->registrations()->count());
        $this->assertSame(1, $existingRegistration->payment()->count());
        $this->assertSame($userBefore, $existing->fresh()->getAttributes());
        $this->assertSame($paymentBefore, $payment->fresh()->getAttributes());
        $this->assertSame($registrationBefore, $existingRegistration->fresh()->getAttributes());
        Notification::assertNothingSent();
    }

    public function test_participant_requires_email_and_invalid_submission_creates_no_accounts(): void
    {
        [$user, $registration, $payload] = $this->presenterSubmission();
        $payload['authors'] = [['name' => 'No Email', 'country' => 'Indonesia', 'participant' => 1]];
        $userCount = User::query()->count();

        $this->actingAs($user)->post(route('participant.submissions.store'), $payload)
            ->assertSessionHasErrors('authors.0.email');

        $this->assertSame($userCount, User::query()->count());
        $this->assertDatabaseMissing('submissions', ['user_id' => $user->id]);
        Notification::assertNothingSent();
    }

    public function test_duplicate_emails_are_rejected_and_limit_is_ten_coauthors(): void
    {
        [$user, $registration, $payload] = $this->presenterSubmission();
        $payload['authors'] = array_fill(0, 11, ['name' => 'Duplicate', 'email' => 'duplicate@example.test']);

        $this->actingAs($user)->post(route('participant.submissions.store'), $payload)
            ->assertSessionHasErrors(['authors', 'authors.0.email']);

        $this->assertDatabaseMissing('submissions', ['user_id' => $user->id]);
    }

    public function test_empty_optional_row_keeps_main_author_without_extra_bill(): void
    {
        [$user, $registration, $payload] = $this->presenterSubmission();
        $payload['authors'] = [['name' => '', 'email' => '', 'affiliation' => '', 'country' => 'Indonesia']];

        $this->actingAs($user)->post(route('participant.submissions.store'), $payload)->assertSessionHasNoErrors()->assertRedirect();

        $submission = Submission::query()->with('authors.registration.payment')->whereBelongsTo($user)->firstOrFail();
        $this->assertCount(1, $submission->authors);
        $this->assertSame($user->id, $submission->authors->first()->user_id);
        $this->assertSame(1, Registration::query()->where('conference_id', $registration->conference_id)->count());
    }

    public function test_account_invitation_sets_password_with_single_use_email_token(): void
    {
        $user = User::factory()->unverified()->create();
        $message = (new CoauthorAccountInvitation)->toMail($user);
        $token = basename(parse_url($message->actionUrl, PHP_URL_PATH));
        $this->assertTrue(Password::tokenExists($user, $token));
        $this->get($message->actionUrl)->assertSee('Set your password');
        $payload = ['token' => $token, 'email' => $user->email, 'password' => 'new-password123', 'password_confirmation' => 'new-password123'];

        $this->post(route('coauthor.account.update'), $payload)->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('new-password123', $user->fresh()->password));
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->post(route('coauthor.account.update'), $payload)->assertSessionHasErrors('email');
    }

    /** @return array{0: User, 1: Registration, 2: array<string, mixed>} */
    private function presenterSubmission(): array
    {
        Queue::fake();
        Notification::fake();
        $conference = Conference::query()->where('slug', 'icleh-2026')->firstOrFail();
        $fee = RegistrationFee::query()->whereBelongsTo($conference)->where('participant_type', 'presenter')->firstOrFail();
        $user = User::factory()->create();
        $registration = Registration::factory()->for($conference)->for($user)->for($fee, 'fee')->create();

        return [$user, $registration, ['title' => 'Coauthor research', 'conference_topic_id' => $conference->topics()->firstOrFail()->id]];
    }
}
