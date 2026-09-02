<?php

namespace Database\Factories;

use App\Models\Conference;
use App\Models\RegistrationFee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegistrationFee>
 */
class RegistrationFeeFactory extends Factory
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
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'participant_type' => 'presenter',
            'attendance_mode' => 'online',
            'amount' => 1250000,
            'currency' => 'IDR',
            'active' => true,
            'registration_start' => now()->toDateString(),
            'registration_end' => now()->addMonth()->toDateString(),
        ];
    }
}
