<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SubmissionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SubmissionDecisionRequest;
use App\Models\Submission;
use App\Services\ConferenceContext;
use App\Services\SubmissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubmissionDecisionController extends Controller
{
    public function __construct(private SubmissionService $submissionService, private ConferenceContext $conferenceContext) {}

    public function show(Submission $submission): View
    {
        abort_unless($submission->conference_id === $this->conferenceContext->current()->id, 404);
        $submission->load(['user', 'topic', 'authors', 'histories', 'reviewAssignments.review.scores', 'reviewAssignments.reviewer']);
        $review = $submission->reviewAssignments->firstWhere('reviewer_id', request()->user()->id)?->review;

        return view('admin.submission-review', compact('submission', 'review'));
    }

    public function file(Submission $submission): StreamedResponse
    {
        abort_unless($submission->conference_id === $this->conferenceContext->current()->id, 404);
        abort_unless($submission->abstract_file && Storage::exists($submission->abstract_file), 404);

        return Storage::download($submission->abstract_file, basename($submission->abstract_file), ['X-Content-Type-Options' => 'nosniff']);
    }

    public function update(SubmissionDecisionRequest $request, Submission $submission): JsonResponse
    {
        abort_unless($submission->conference_id === $this->conferenceContext->current()->id, 404);
        $submission = $this->submissionService->changeStatus(
            $submission,
            SubmissionStatus::from((string) $request->validated('status')),
            $request->user(),
            $request->input('notes'),
        );

        return response()->json([
            'message' => 'Submission status updated.',
            'submission' => [
                'uuid' => $submission->uuid,
                'status' => $submission->status->value,
                'loa' => $submission->loaDocument?->verification_code,
            ],
        ]);
    }
}
