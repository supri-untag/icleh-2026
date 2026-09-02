<?php

namespace Database\Factories;

use App\Models\Conference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Conference>
 */
class ConferenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'ICLEH '.fake()->year(),
            'slug' => fake()->unique()->slug(),
            'edition' => 'Conference',
            'theme' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'start_date' => now()->addMonth()->toDateString(),
            'end_date' => now()->addMonth()->addDay()->toDateString(),
            'timezone' => 'Asia/Jakarta',
            'mode' => 'hybrid',
            'venue_name' => fake()->company(),
            'location' => fake()->city(),
            'registration_requires_verified_payment' => false,
            'active' => true,
        ];
    }
}
