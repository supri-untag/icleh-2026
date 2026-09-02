<?php

namespace Database\Factories;

use App\Models\Conference;
use App\Models\ConferenceTopic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ConferenceTopic>
 */
class ConferenceTopicFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'conference_id' => Conference::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->sentence(),
            'keywords' => fake()->words(4),
            'display_order' => fake()->numberBetween(1, 10),
            'active' => true,
        ];
    }
}
