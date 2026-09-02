<?php

namespace App\Http\Controllers\Participant;

use App\DTOs\SubmissionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Participant\SubmissionRequest;
use App\Models\Registration;
use App\Models\Submission;
use App\Services\ConferenceContext;
use App\Services\SubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubmissionController extends Controller
{
    public function __construct(
        private ConferenceContext $conferenceContext,
        private SubmissionService $submissionService,
    ) {}

    public function index(): View
    {
        $conference = $this->conferenceContext->current();
        $submissions = Submission::query()
            ->whereBelongsTo($conference)
            ->whereBelongsTo(request()->user())
            ->with(['topic', 'loaDocument'])
            ->latest()
            ->get();

        return view('participant.submissions', [
            'conference' => $conference,
            'submissions' => $submissions,
        ]);
    }

    public function create(): View
    {
        $conference = $this->conferenceContext->current()->load([
            'topics' => fn ($query) => $query->active()->orderBy('display_order'),
        ]);
        $registration = Registration::query()
            ->whereBelongsTo($conference)
            ->whereBelongsTo(request()->user())
            ->first();

        return view('participant.submission-show', [
            'conference' => $conference,
            'submission' => null,
            'registration' => $registration,
        ]);
    }

    public function store(SubmissionRequest $request): RedirectResponse
    {
        $submission = $this->submissionService->create(
            $request->user(),
            SubmissionData::fromArray($request->validated()),
        );

        return redirect()
            ->route('participant.submissions.show', $submission)
            ->with('status', 'Abstract submitted.');
    }

    public function show(Submission $submission): View
    {
        abort_unless((int) $submission->user_id === (int) request()->user()->id, 404);

        $submission->load(['conference', 'registration', 'topic', 'authors', 'files', 'histories', 'loaDocument', 'reviewAssignments.review.scores']);

        return view('participant.submission-show', [
            'conference' => $submission->conference,
            'submission' => $submission,
            'registration' => $submission->registration,
        ]);
    }
}
