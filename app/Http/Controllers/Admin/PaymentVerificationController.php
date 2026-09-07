<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectPaymentRequest;
use App\Http\Requests\Admin\VerifyPaymentRequest;
use App\Models\Payment;
use App\Services\ConferenceContext;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentVerificationController extends Controller
{
    public function __construct(private PaymentService $paymentService, private ConferenceContext $conferenceContext) {}

    private function checkConference(Payment $payment): void
    {
        abort_unless($payment->registration->conference_id === $this->conferenceContext->current()->id, 404);
    }

    public function show(Payment $payment): View
    {
        $payment->load(['registration.user', 'registration.fee', 'verifier']);
        $this->checkConference($payment);

        return view('admin.payment-review', compact('payment'));
    }

    public function proof(Payment $payment): StreamedResponse
    {
        $this->checkConference($payment);
        abort_unless($payment->proof_file && Storage::exists($payment->proof_file), 404);

        return Storage::download($payment->proof_file, basename($payment->proof_file), ['X-Content-Type-Options' => 'nosniff']);
    }

    public function verify(VerifyPaymentRequest $request, Payment $payment): JsonResponse
    {
        $this->checkConference($payment);
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
        $this->checkConference($payment);
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
