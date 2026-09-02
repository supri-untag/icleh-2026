<?php

namespace App\DTOs;

use App\Models\RegistrationFee;

class RegistrationData
{
    public function __construct(
        public readonly int $conferenceId,
        public readonly int $registrationFeeId,
        public readonly string $participantType,
        public readonly ?string $attendanceMode,
        public readonly ?string $notes,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data, RegistrationFee $fee): self
    {
        return new self(
            conferenceId: (int) $fee->conference_id,
            registrationFeeId: (int) $fee->id,
            participantType: (string) $data['participant_type'],
            attendanceMode: $data['attendance_mode'] ?? null,
            notes: $data['notes'] ?? null,
        );
    }
}
