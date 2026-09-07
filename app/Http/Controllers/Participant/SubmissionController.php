<?php

namespace App\Http\Controllers\Participant;

use App\DTOs\SubmissionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Participant\SubmissionRequest;
use App\Models\Country;
use App\Models\Registration;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Services\ConferenceContext;
use App\Services\SubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            'countries' => Country::query()->active()->ordered()->get(['name']),
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

    public function file(Submission $submission, ?SubmissionFile $submissionFile = null): StreamedResponse
    {
        abort_unless((int) $submission->user_id === (int) request()->user()->id, 404);
        if ($submissionFile) {
            abort_unless((int) $submissionFile->submission_id === (int) $submission->id, 404);
        }

        $path = $submissionFile?->storage_path ?? $submission->abstract_file;
        abort_unless($path && Storage::exists($path), 404);

        $name = $submissionFile?->original_filename ?? basename($path);
        $headers = ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store'];

        if (! request()->boolean('download') && Storage::mimeType($path) === 'application/pdf') {
            return Storage::response($path, $name, $headers);
        }

        return Storage::download($path, $name, $headers);
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
