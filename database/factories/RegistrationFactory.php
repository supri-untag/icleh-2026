<?php

namespace Database\Factories;

use App\Enums\RegistrationStatus;
use App\Models\Conference;
use App\Models\Registration;
use App\Models\RegistrationFee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Registration>
 */
class RegistrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conference_id' => Conference::factory(),
            'user_id' => User::factory(),
            'registration_fee_id' => RegistrationFee::factory(),
            'registration_code' => 'REG-'.Str::upper(Str::random(8)),
            'participant_type' => 'presenter',
            'attendance_mode' => 'online',
            'status' => RegistrationStatus::WaitingPayment,
            'registered_at' => now(),
        ];
    }
}
