<?php

namespace App\Enums;

enum RegistrationStatus: string
{
    case Draft = 'draft';
    case Registered = 'registered';
    case WaitingPayment = 'waiting_payment';
    case PaymentSubmitted = 'payment_submitted';
    case PaymentVerified = 'payment_verified';
    case Confirmed = 'confirmed';
    case Attended = 'attended';
    case Completed = 'completed';
    case PaymentRejected = 'payment_rejected';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Registered => 'Registered',
            self::WaitingPayment => 'Waiting Payment',
            self::PaymentSubmitted => 'Payment Submitted',
            self::PaymentVerified => 'Payment Verified',
            self::Confirmed => 'Confirmed',
            self::Attended => 'Attended',
            self::Completed => 'Completed',
            self::PaymentRejected => 'Payment Rejected',
            self::Cancelled => 'Cancelled',
        };
    }
}
