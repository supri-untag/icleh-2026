<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Conference;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPaymentVerificationTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_admin_can_read_proof_and_verify_payment(): void
    {
        Queue::fake();
        Storage::fake();
        $admin = User::factory()->create();
        $admin->assignRole(UserRole::Admin);
        $conference = Conference::query()->where('slug', 'icleh-2026')->firstOrFail();
        $registration = Registration::factory()->for($conference)->create();
        Storage::put('payments/proof.pdf', 'payment proof');
        $payment = Payment::factory()->for($registration)->create(['status' => 'submitted', 'proof_file' => 'payments/proof.pdf']);

        $this->actingAs($admin)->get(route('admin.payments.review', $payment))->assertOk()->assertSee('Verify Payment');
        $this->get(route('admin.payments.proof', $payment))->assertDownload('proof.pdf');
        $this->postJson(route('admin.payments.verify', $payment), ['notes' => 'Transfer matches.'])->assertOk();

        $this->assertSame('verified', $payment->fresh()->status->value);
        $this->assertSame($admin->id, $payment->fresh()->verified_by);
        $this->assertSame('confirmed', $registration->fresh()->status->value);
        $this->getJson(route('admin.registrations.receipt', $registration))->assertOk()->assertJsonPath('filename', $registration->registration_code.'.pdf');
        $receipt = $this->getJson(route('admin.payments.receipt', $payment))->assertOk()->assertJsonPath('filename', $payment->payment_code.'.pdf');
        $this->assertStringContainsString($registration->user->name, json_encode($receipt->json()));
        $this->assertStringContainsString('Verified', json_encode($receipt->json()));
        $this->actingAs($registration->user)->get(route('admin.payments.proof', $payment))->assertForbidden();
    }

    public function test_admin_can_reject_payment_with_reason(): void
    {
        Queue::fake();
        $admin = User::factory()->create();
        $admin->assignRole(UserRole::Admin);
        $conference = Conference::query()->where('slug', 'icleh-2026')->firstOrFail();
        $registration = Registration::factory()->for($conference)->create();
        $payment = Payment::factory()->for($registration)->create(['status' => 'submitted', 'proof_file' => 'proof.jpg']);

        $this->actingAs($admin)->postJson(route('admin.payments.reject', $payment), ['rejection_reason' => 'Amount does not match.'])->assertOk();

        $this->assertSame('rejected', $payment->fresh()->status->value);
        $this->assertSame('Amount does not match.', $payment->fresh()->rejection_reason);
        $this->assertSame('payment_rejected', $registration->fresh()->status->value);
        $this->getJson(route('admin.payments.receipt', $payment))->assertUnprocessable();
        $this->actingAs($registration->user)->getJson(route('admin.registrations.receipt', $registration))->assertForbidden();
    }
}
