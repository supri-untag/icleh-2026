<?php

namespace App\Services;

use App\DTOs\Mail\SendMailData;
use App\DTOs\PaymentProofData;
use App\Enums\PaymentStatus;
use App\Enums\RegistrationStatus;
use App\Models\AuditLog;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\User;
use App\Services\Mail\MailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(private MailService $mailService) {}

    public function submitProof(User $user, Registration $registration, PaymentProofData $data): Payment
    {
        $registration->loadMissing(['conference', 'payment', 'user', 'fee']);

        if ((int) $registration->user_id !== (int) $user->id) {
            throw ValidationException::withMessages([
                'registration' => 'The selected registration does not belong to your account.',
            ]);
        }

        return DB::transaction(function () use ($registration, $data, $user): Payment {
            $payment = $registration->payment()->firstOrFail();
            $path = $data->proofFile->store('payments/'.$registration->uuid);

            $payment->update([
                'proof_file' => $path,
                'paid_at' => $data->paidAt,
                'submitted_at' => now(),
                'status' => PaymentStatus::Submitted,
                'rejection_reason' => null,
                'notes' => $data->notes,
            ]);

            $registration->update([
                'status' => RegistrationStatus::PaymentSubmitted,
            ]);

            AuditLog::query()->create([
                'actor_id' => $user->id,
                'action' => 'payment.submitted',
                'subject_type' => Payment::class,
                'subject_id' => (string) $payment->id,
                'after' => $payment->only(['payment_code', 'status', 'submitted_at']),
            ]);

            $this->mailService->queue(SendMailData::fromArray([
                'to' => $registration->user->email,
                'template' => 'payment_submitted',
                'subject' => 'Payment Proof Submitted - ICLEH 2026',
                'conference_id' => $registration->conference_id,
                'user_id' => $registration->user_id,
                'data' => [
                    'participant_name' => $registration->user->name,
                    'conference_name' => $registration->conference->name,
                    'payment_amount' => $payment->formattedAmount(),
                ],
            ]));

            return $payment->refresh()->load(['registration.user', 'registration.fee']);
        });
    }

    public function verify(Payment $payment, User $verifier, ?string $notes = null): Payment
    {
        return DB::transaction(function () use ($payment, $verifier, $notes): Payment {
            $payment->loadMissing(['registration.conference', 'registration.user', 'registration.fee']);

            $before = $payment->only(['status', 'verified_at', 'verified_by']);

            $payment->update([
                'status' => PaymentStatus::Verified,
                'verified_at' => now(),
                'verified_by' => $verifier->id,
                'notes' => $notes,
                'rejection_reason' => null,
            ]);

            $payment->registration->update([
                'status' => RegistrationStatus::Confirmed,
                'confirmed_at' => now(),
            ]);

            AuditLog::query()->create([
                'actor_id' => $verifier->id,
                'action' => 'payment.verified',
                'subject_type' => Payment::class,
                'subject_id' => (string) $payment->id,
                'before' => $before,
                'after' => $payment->only(['status', 'verified_at', 'verified_by']),
            ]);

            $this->mailService->queue(SendMailData::fromArray([
                'to' => $payment->registration->user->email,
                'template' => 'payment_verified',
                'subject' => 'Payment Verified - ICLEH 2026',
                'conference_id' => $payment->registration->conference_id,
                'user_id' => $payment->registration->user_id,
                'data' => [
                    'participant_name' => $payment->registration->user->name,
                    'conference_name' => $payment->registration->conference->name,
                    'payment_amount' => $payment->formattedAmount(),
                ],
            ]));

            return $payment->refresh()->load(['registration.user', 'registration.fee', 'verifier']);
        });
    }

    public function reject(Payment $payment, User $verifier, string $reason): Payment
    {
        return DB::transaction(function () use ($payment, $verifier, $reason): Payment {
            $payment->loadMissing(['registration.conference', 'registration.user', 'registration.fee']);

            $before = $payment->only(['status', 'rejection_reason']);

            $payment->update([
                'status' => PaymentStatus::Rejected,
                'verified_at' => now(),
                'verified_by' => $verifier->id,
                'rejection_reason' => $reason,
            ]);

            $payment->registration->update([
                'status' => RegistrationStatus::PaymentRejected,
            ]);

            AuditLog::query()->create([
                'actor_id' => $verifier->id,
                'action' => 'payment.rejected',
                'subject_type' => Payment::class,
                'subject_id' => (string) $payment->id,
                'before' => $before,
                'after' => $payment->only(['status', 'rejection_reason']),
            ]);

            $this->mailService->queue(SendMailData::fromArray([
                'to' => $payment->registration->user->email,
                'template' => 'payment_rejected',
                'subject' => 'Payment Rejected - ICLEH 2026',
                'conference_id' => $payment->registration->conference_id,
                'user_id' => $payment->registration->user_id,
                'data' => [
                    'participant_name' => $payment->registration->user->name,
                    'conference_name' => $payment->registration->conference->name,
                    'payment_amount' => $payment->formattedAmount(),
                    'rejection_reason' => $reason,
                ],
            ]));

            return $payment->refresh()->load(['registration.user', 'registration.fee', 'verifier']);
        });
    }
}
