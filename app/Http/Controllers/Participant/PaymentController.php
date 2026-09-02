<?php

namespace App\Http\Controllers\Participant;

use App\DTOs\PaymentProofData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Participant\PaymentProofRequest;
use App\Models\Registration;
use App\Services\ConferenceContext;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

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

    public function store(PaymentProofRequest $request): RedirectResponse
    {
        $conference = $this->conferenceContext->current();
        $registration = $this->currentRegistration($conference->id);

        abort_unless($registration, 404);

        $this->paymentService->submitProof(
            $request->user(),
            $registration,
            PaymentProofData::fromArray($request->validated()),
        );

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
