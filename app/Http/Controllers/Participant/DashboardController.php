<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Services\ConferenceContext;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private ConferenceContext $conferenceContext) {}

    public function __invoke(): View
    {
        $conference = $this->conferenceContext->current();
        $user = request()->user()->load([
            'profile',
            'registrations' => fn ($query) => $query->whereBelongsTo($conference)->with(['conference', 'fee', 'payment']),
            'submissions' => fn ($query) => $query->whereBelongsTo($conference)->with(['topic', 'loaDocument', 'schedules.chamber']),
        ]);

        return view('participant.dashboard', [
            'conference' => $conference,
            'registration' => $user->registrations->first(),
            'submission' => $user->submissions->first(),
            'user' => $user,
        ]);
    }
}
