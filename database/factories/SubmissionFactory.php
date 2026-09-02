<?php

namespace Database\Factories;

use App\Enums\SubmissionStatus;
use App\Models\Conference;
use App\Models\ConferenceTopic;
use App\Models\Registration;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Submission>
 */
class SubmissionFactory extends Factory
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
            'registration_id' => Registration::factory(),
            'conference_topic_id' => ConferenceTopic::factory(),
            'submission_code' => 'ABS-'.Str::upper(Str::random(8)),
            'title' => fake()->sentence(),
            'abstract_text' => fake()->paragraph(),
            'keywords' => fake()->words(3),
            'corresponding_author' => fake()->name(),
            'country' => 'Indonesia',
            'status' => SubmissionStatus::AbstractSubmitted,
            'submitted_at' => now(),
        ];
    }
}
