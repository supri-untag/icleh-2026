<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectPaymentRequest;
use App\Http\Requests\Admin\VerifyPaymentRequest;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;

class PaymentVerificationController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function verify(VerifyPaymentRequest $request, Payment $payment): JsonResponse
    {
        $payment = $this->paymentService->verify($payment, $request->user(), $request->input('notes'));

        return response()->json([
            'message' => 'Payment verified.',
            'payment' => [
                'uuid' => $payment->uuid,
                'status' => $payment->status->value,
            ],
        ]);
    }

    public function reject(RejectPaymentRequest $request, Payment $payment): JsonResponse
    {
        $payment = $this->paymentService->reject(
            $payment,
            $request->user(),
            (string) $request->validated('rejection_reason'),
        );

        return response()->json([
            'message' => 'Payment rejected.',
            'payment' => [
                'uuid' => $payment->uuid,
                'status' => $payment->status->value,
            ],
        ]);
    }
}
