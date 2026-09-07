<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Country>
 */
class CountryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->country();

        return [
            'iso2' => fake()->unique()->countryCode(),
            'name' => $name,
            'active' => true,
            'display_order' => fake()->numberBetween(1, 250),
        ];
    }
}
