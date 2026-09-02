<?php

namespace App\Services;

use App\DTOs\Mail\SendMailData;
use App\DTOs\RegistrationData;
use App\Enums\RegistrationStatus;
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\RegistrationFee;
use App\Models\Role;
use App\Models\User;
use App\Services\Mail\MailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegistrationService
{
    public function __construct(private MailService $mailService) {}

    public function register(User $user, RegistrationData $data): Registration
    {
        return DB::transaction(function () use ($user, $data): Registration {
            $registration = Registration::query()
                ->with('payment')
                ->where('conference_id', $data->conferenceId)
                ->whereBelongsTo($user)
                ->first() ?? new Registration([
                    'conference_id' => $data->conferenceId,
                    'user_id' => $user->id,
                    'registration_code' => $this->newCode('ICLEH-REG', Registration::class, 'registration_code'),
                ]);
            $registrationAlreadyExists = $registration->exists;

            $registration->fill([
                'registration_fee_id' => $data->registrationFeeId,
                'participant_type' => $data->participantType,
                'attendance_mode' => $data->attendanceMode,
                'status' => RegistrationStatus::WaitingPayment,
                'registered_at' => now(),
                'notes' => $data->notes,
            ]);
            $registration->save();

            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'full_name' => $user->name,
                    'whatsapp' => $user->whatsapp,
                    'institution' => $user->institution,
                    'country' => $user->country,
                    'participant_type' => $data->participantType,
                    'attendance_mode' => $data->attendanceMode,
                ],
            );

            $this->syncParticipantRole($user, $data->participantType);

            $fee = RegistrationFee::query()->with('conference')->findOrFail($data->registrationFeeId);
            $existingPayment = $registrationAlreadyExists ? $registration->payment : null;
            Payment::query()->updateOrCreate(
                ['registration_id' => $registration->id],
                [
                    'payment_code' => $existingPayment?->payment_code
                        ?? $this->newCode('ICLEH-PAY', Payment::class, 'payment_code'),
                    'method' => 'manual_transfer',
                    'amount' => $fee->amount,
                    'currency' => $fee->currency,
                    'status' => $existingPayment?->status ?? 'waiting',
                ],
            );

            AuditLog::query()->create([
                'actor_id' => $user->id,
                'action' => 'registration.created',
                'subject_type' => Registration::class,
                'subject_id' => (string) $registration->id,
                'after' => $registration->only(['registration_code', 'participant_type', 'attendance_mode', 'status']),
            ]);

            $this->mailService->queue(SendMailData::fromArray([
                'to' => $user->email,
                'template' => 'registration_success',
                'subject' => 'Registration Received - ICLEH 2026',
                'conference_id' => $data->conferenceId,
                'user_id' => $user->id,
                'data' => [
                    'participant_name' => $user->name,
                    'registration_code' => $registration->registration_code,
                    'conference_name' => $fee->conference->name,
                    'payment_amount' => $fee->formattedAmount(),
                ],
            ]));

            return $registration->refresh()->load(['conference', 'fee', 'payment']);
        });
    }

    private function syncParticipantRole(User $user, string $participantType): void
    {
        $roleName = $participantType === 'presenter' ? UserRole::Presenter->value : UserRole::Participant->value;
        $role = Role::query()->where('name', $roleName)->first();

        if ($role) {
            $user->roles()->syncWithoutDetaching([$role->id]);
        }
    }

    private function newCode(string $prefix, string $modelClass, string $column): string
    {
        do {
            $code = $prefix.'-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
        } while ($modelClass::query()->where($column, $code)->exists());

        return $code;
    }
}
