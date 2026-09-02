<?php

namespace Database\Factories;

use App\Models\LoaDocument;
use App\Models\Submission;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LoaDocument>
 */
class LoaDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'submission_id' => Submission::factory(),
            'loa_number' => 'LOA-'.Str::upper(Str::random(8)),
            'verification_code' => 'V-'.Str::upper(Str::random(10)),
            'issued_date' => now()->toDateString(),
            'signer_name' => 'Dean',
            'signer_title' => 'Faculty of Law',
            'status' => 'issued',
        ];
    }
}
