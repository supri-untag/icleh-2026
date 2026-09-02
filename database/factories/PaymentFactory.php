<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Registration;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'registration_id' => Registration::factory(),
            'payment_code' => 'PAY-'.Str::upper(Str::random(8)),
            'method' => 'manual_transfer',
            'amount' => 1250000,
            'currency' => 'IDR',
            'status' => PaymentStatus::Waiting,
        ];
    }
}
