<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\LoaDocument;
use App\Services\ConferenceContext;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function __construct(private ConferenceContext $conferenceContext) {}

    public function index(): View
    {
        $conference = $this->conferenceContext->current()->load([
            'venue',
            'days' => fn ($query) => $query->orderBy('display_order'),
            'days.schedules' => fn ($query) => $query
                ->published()
                ->with(['chamber', 'speaker', 'submission'])
                ->orderBy('start_time'),
        ]);
        $user = request()->user()->load([
            'registrations' => fn ($query) => $query->whereBelongsTo($conference)->with(['payment', 'attendances.schedule.chamber']),
            'submissions' => fn ($query) => $query->whereBelongsTo($conference)->with('loaDocument'),
        ]);
        $registration = $user->registrations->first();
        $certificates = collect();

        if ($registration || $user->submissions->isNotEmpty()) {
            $submissionIds = $user->submissions->pluck('id');

            $certificates = Certificate::query()
                ->whereBelongsTo($conference)
                ->where(function ($query) use ($registration, $submissionIds): void {
                    if ($registration) {
                        $query->where('registration_id', $registration->id);
                    }

                    if ($submissionIds->isNotEmpty()) {
                        if ($registration) {
                            $query->orWhereIn('submission_id', $submissionIds);
                        } else {
                            $query->whereIn('submission_id', $submissionIds);
                        }
                    }
                })
                ->with('submission')
                ->latest('issued_date')
                ->latest()
                ->get();
        }

        return view('participant.documents', [
            'conference' => $conference,
            'registration' => $registration,
            'submissions' => $user->submissions,
            'certificates' => $certificates,
        ]);
    }

    public function loa(LoaDocument $loaDocument): View
    {
        $loaDocument->loadMissing(['submission.conference', 'submission.user', 'submission.authors']);

        abort_unless((int) $loaDocument->submission->user_id === (int) request()->user()->id, 404);

        return view('documents.loa', [
            'loa' => $loaDocument,
        ]);
    }
}
