<?php

namespace Tests\Feature;

use App\Models\Conference;
use App\Models\ConferenceTopic;
use App\Models\Country;
use App\Models\LoaDocument;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\RegistrationFee;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ParticipantRegistrationPaymentTest extends TestCase
{
    public function test_profile_update_saves_country_relation(): void
    {
        $country = Country::query()->where('iso2', 'DE')->firstOrFail();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('participant.profile.update'), [
                'full_name' => 'Profile User',
                'whatsapp' => '+49123456789',
                'institution' => 'ICLEH Profile Institute',
                'country_id' => $country->id,
                'participant_type' => 'presenter',
                'attendance_mode' => 'offline',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $user->refresh()->load('profile');

        $this->assertSame($country->id, $user->country_id);
        $this->assertSame($country->name, $user->country);
        $this->assertSame($country->id, $user->profile->country_id);
        $this->assertSame($country->name, $user->profile->country);
    }

    public function test_participant_registration_saves_country_relation(): void
    {
        Queue::fake();

        $conference = Conference::query()->where('slug', 'icleh-2026')->firstOrFail();
        $fee = RegistrationFee::query()->whereBelongsTo($conference)->where('participant_type', 'presenter')->firstOrFail();
        $country = Country::query()->where('iso2', 'SG')->firstOrFail();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('participant.registration.store'), [
                'registration_fee_id' => $fee->id,
                'country_id' => $country->id,
                'attendance_mode' => 'online',
                'notes' => 'Country relation test.',
            ])
            ->assertRedirect(route('participant.payment'))
            ->assertSessionHasNoErrors();

        $registration = Registration::query()
            ->whereBelongsTo($conference)
            ->whereBelongsTo($user)
            ->firstOrFail();

        $user->refresh()->load('profile');

        $this->assertSame($country->id, $registration->country_id);
        $this->assertSame('presenter', $registration->participant_type);
        $this->assertSame($country->id, $user->country_id);
        $this->assertSame($country->name, $user->country);
        $this->assertSame($country->id, $user->profile->country_id);
    }

    public function test_presenter_fee_requires_attendance_even_when_client_sends_general_type(): void
    {
        $fee = RegistrationFee::query()->where('participant_type', 'presenter')->firstOrFail();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('participant.registration.store'), [
            'registration_fee_id' => $fee->id,
            'country_id' => $user->country_id,
            'participant_type' => 'general',
        ])->assertSessionHasErrors('attendance_mode');

        $this->assertDatabaseMissing('registrations', ['user_id' => $user->id]);
    }

    public function test_general_fee_determines_type_and_price_without_attendance(): void
    {
        Queue::fake();
        $fee = RegistrationFee::query()->where('participant_type', 'general')->firstOrFail();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('participant.registration.store'), [
            'registration_fee_id' => $fee->id,
            'country_id' => $user->country_id,
            'participant_type' => 'presenter',
        ])->assertRedirect(route('participant.payment'))->assertSessionHasNoErrors();

        $registration = Registration::query()->whereBelongsTo($user)->firstOrFail();
        $this->assertSame('general', $registration->participant_type);
        $this->assertSame('general', $user->fresh()->profile->participant_type);
        $this->assertSame($fee->amount, $registration->payment->amount);
        $this->assertNull($registration->attendance_mode);
    }

    public function test_verified_participant_can_render_portal_pages(): void
    {
        $conference = Conference::query()->where('slug', 'icleh-2026')->firstOrFail();
        $fee = RegistrationFee::query()->whereBelongsTo($conference)->where('participant_type', 'presenter')->firstOrFail();
        $topic = ConferenceTopic::query()->whereBelongsTo($conference)->firstOrFail();
        $user = User::factory()->create(['name' => 'Participant User']);
        $registration = Registration::factory()
            ->for($conference)
            ->for($user)
            ->for($fee, 'fee')
            ->create();
        Payment::factory()->for($registration)->create();
        $submission = Submission::factory()
            ->for($conference)
            ->for($user)
            ->for($registration)
            ->for($topic, 'topic')
            ->create(['title' => 'Human Rights and Artificial Intelligence']);
        $loaDocument = LoaDocument::factory()->for($submission)->create();

        $pages = [
            route('participant.dashboard') => 'Dashboard',
            route('participant.profile') => 'My Profile',
            route('participant.registration') => 'Registration',
            route('participant.payment') => 'Payment',
            route('participant.submissions') => 'My Submission',
            route('participant.submissions.create') => 'Submit Abstract',
            route('participant.submissions.show', $submission) => 'Submission Detail',
            route('participant.loa') => 'Letter of Acceptance',
            route('participant.loa.show', $loaDocument) => 'Letter of Acceptance',
            route('participant.program') => 'Conference Program',
            route('participant.attendance') => 'Attendance / QR',
            route('participant.certificates') => 'Certificates',
            route('participant.notifications') => 'Notifications',
        ];

        foreach ($pages as $url => $heading) {
            $this->actingAs($user)
                ->get($url)
                ->assertOk()
                ->assertSee($heading);
        }
    }

    public function test_verified_participant_can_view_uploaded_payment_proof(): void
    {
        Storage::fake('local');

        $conference = Conference::query()->where('slug', 'icleh-2026')->firstOrFail();
        $fee = RegistrationFee::query()->whereBelongsTo($conference)->where('participant_type', 'presenter')->firstOrFail();
        $user = User::factory()->create();
        $registration = Registration::factory()
            ->for($conference)
            ->for($user)
            ->for($fee, 'fee')
            ->create();
        $proofPath = UploadedFile::fake()
            ->image('proof.jpg')
            ->storeAs('payments/'.$registration->uuid, 'proof.jpg', 'local');

        Payment::factory()->for($registration)->create([
            'proof_file' => $proofPath,
            'submitted_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('participant.payment'))
            ->assertOk()
            ->assertSee('Payment locked.')
            ->assertDontSee('data-payment-form', false)
            ->assertSee('View Proof');

        $this->actingAs($user)
            ->get(route('participant.payment.proof'))
            ->assertOk();

        $this->actingAs(User::factory()->create())
            ->get(route('participant.payment.proof'))
            ->assertNotFound();
    }

    public function test_payment_proof_dropzone_upload_returns_json_redirect(): void
    {
        Queue::fake();
        Storage::fake('local');

        $conference = Conference::query()->where('slug', 'icleh-2026')->firstOrFail();
        $fee = RegistrationFee::query()->whereBelongsTo($conference)->where('participant_type', 'presenter')->firstOrFail();
        $user = User::factory()->create();
        $registration = Registration::factory()
            ->for($conference)
            ->for($user)
            ->for($fee, 'fee')
            ->create();
        $payment = Payment::factory()->for($registration)->create();

        $this->actingAs($user)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('participant.payment.store'), [
                'paid_at' => '2026-09-02',
                'proof_file' => UploadedFile::fake()->image('proof.jpg'),
            ])
            ->assertOk()
            ->assertJson([
                'message' => 'Payment proof submitted.',
                'redirect_url' => route('participant.payment'),
            ]);

        $payment->refresh();

        $this->assertNotNull($payment->proof_file);
        Storage::disk('local')->assertExists($payment->proof_file);
    }

    public function test_uploaded_payments_block_reuploads_and_registration_changes(): void
    {
        Queue::fake();
        Storage::fake('local');
        $fee = RegistrationFee::query()->where('participant_type', 'general')->firstOrFail();

        foreach (['submitted', 'verified'] as $status) {
            $user = User::factory()->create();
            $registration = Registration::factory()->for($user)->for($fee, 'fee')->create(['conference_id' => $fee->conference_id]);
            $payment = Payment::factory()->for($registration)->create([
                'status' => $status,
                'proof_file' => 'payments/original.jpg',
            ]);
            $paymentBefore = $payment->fresh()->getAttributes();
            $registrationBefore = $registration->fresh()->getAttributes();

            $this->actingAs($user)->postJson(route('participant.payment.store'), [
                'paid_at' => '2026-09-02',
                'proof_file' => UploadedFile::fake()->image('replacement.jpg'),
            ])->assertUnprocessable()->assertJsonValidationErrors('proof_file');

            $this->actingAs($user)->post(route('participant.registration.store'), [
                'registration_fee_id' => $fee->id,
                'country_id' => $user->country_id,
            ])->assertSessionHasErrors('registration_fee_id');

            $this->assertSame($paymentBefore, $payment->fresh()->getAttributes());
            $this->assertSame($registrationBefore, $registration->fresh()->getAttributes());
        }

        $this->assertSame([], Storage::disk('local')->allFiles());
        Queue::assertNothingPushed();
    }

    public function test_rejected_payment_can_receive_corrected_proof(): void
    {
        Queue::fake();
        Storage::fake('local');
        $user = User::factory()->create();
        $registration = Registration::factory()->for($user)->for(Conference::query()->where('slug', 'icleh-2026')->firstOrFail())->create();
        $payment = Payment::factory()->for($registration)->create([
            'status' => 'rejected',
            'proof_file' => 'payments/rejected.jpg',
            'rejection_reason' => 'Unreadable proof',
        ]);

        $this->actingAs($user)->postJson(route('participant.payment.store'), [
            'paid_at' => '2026-09-02',
            'proof_file' => UploadedFile::fake()->image('corrected.jpg'),
        ])->assertOk();

        $payment->refresh();
        $this->assertSame('submitted', $payment->status->value);
        $this->assertNull($payment->rejection_reason);
        Storage::disk('local')->assertExists($payment->proof_file);
    }
}
