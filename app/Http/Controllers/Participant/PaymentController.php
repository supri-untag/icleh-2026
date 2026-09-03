<?php

namespace App\Http\Controllers\Participant;

use App\DTOs\PaymentProofData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Participant\PaymentProofRequest;
use App\Models\Registration;
use App\Services\ConferenceContext;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    public function __construct(
        private ConferenceContext $conferenceContext,
        private PaymentService $paymentService,
    ) {}

    public function show(): View
    {
        $conference = $this->conferenceContext->current();
        $registration = $this->currentRegistration($conference->id);

        return view('participant.payment', [
            'conference' => $conference,
            'registration' => $registration,
            'payment' => $registration?->payment,
        ]);
    }

    public function proof(): StreamedResponse
    {
        $conference = $this->conferenceContext->current();
        $registration = $this->currentRegistration($conference->id);
        $payment = $registration?->payment;
        $proofPath = $payment?->proof_file;

        abort_unless($proofPath, 404);

        $disk = Storage::disk((string) config('filesystems.default', 'local'));

        abort_unless($disk->exists($proofPath), 404);

        return $disk->response($proofPath, basename($proofPath), [
            'Cache-Control' => 'private, max-age=0, must-revalidate',
        ]);
    }

    public function store(PaymentProofRequest $request): RedirectResponse|JsonResponse
    {
        $conference = $this->conferenceContext->current();
        $registration = $this->currentRegistration($conference->id);

        abort_unless($registration, 404);

        $this->paymentService->submitProof(
            $request->user(),
            $registration,
            PaymentProofData::fromArray($request->validated()),
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Payment proof submitted.',
                'redirect_url' => route('participant.payment'),
            ]);
        }

        return back()->with('status', 'Payment proof submitted.');
    }

    private function currentRegistration(int $conferenceId): ?Registration
    {
        return Registration::query()
            ->where('conference_id', $conferenceId)
            ->whereBelongsTo(request()->user())
            ->with(['conference', 'fee', 'payment'])
            ->first();
    }
}
