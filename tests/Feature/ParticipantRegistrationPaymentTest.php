<?php

namespace Tests\Feature;

use App\Models\Conference;
use App\Models\ConferenceTopic;
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
            ->assertSee('Drop payment proof here')
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
}
