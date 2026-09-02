<?php

namespace App\Http\Controllers\Participant;

use App\DTOs\RegistrationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Participant\RegistrationRequest;
use App\Models\Registration;
use App\Models\RegistrationFee;
use App\Services\ConferenceContext;
use App\Services\RegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function __construct(
        private ConferenceContext $conferenceContext,
        private RegistrationService $registrationService,
    ) {}

    public function show(): View
    {
        $conference = $this->conferenceContext->current()->load([
            'registrationFees' => fn ($query) => $query->active()->orderBy('amount'),
        ]);
        $registration = Registration::query()
            ->whereBelongsTo($conference)
            ->whereBelongsTo(request()->user())
            ->with(['fee', 'payment'])
            ->first();

        return view('participant.registration', [
            'conference' => $conference,
            'registration' => $registration,
        ]);
    }

    public function store(RegistrationRequest $request): RedirectResponse
    {
        $fee = RegistrationFee::query()
            ->active()
            ->findOrFail($request->integer('registration_fee_id'));

        $this->registrationService->register(
            $request->user(),
            RegistrationData::fromArray($request->validated(), $fee),
        );

        return redirect()->route('participant.payment')->with('status', 'Registration saved. Please upload payment proof.');
    }
}
