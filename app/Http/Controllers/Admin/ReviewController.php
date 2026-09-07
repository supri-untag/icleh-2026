<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SubmissionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewRequest;
use App\Models\ReviewAssignment;
use App\Models\Submission;
use App\Models\SubmissionStatusHistory;
use App\Services\ConferenceContext;
use App\Services\Mail\WorkflowMailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function submit(ReviewRequest $request, Submission $submission, ConferenceContext $conferenceContext): RedirectResponse
    {
        abort_unless($submission->conference_id === $conferenceContext->current()->id, 404);

        return DB::transaction(function () use ($request, $submission): RedirectResponse {
            $submission = Submission::query()->lockForUpdate()->findOrFail($submission->id);
            $assignment = $submission->reviewAssignments()->firstOrCreate(
                ['reviewer_id' => $request->user()->id],
                ['assigned_by' => $request->user()->id, 'status' => 'assigned', 'blind_review' => false],
            );

            return $this->store($request, $assignment);
        });
    }

    public function store(ReviewRequest $request, ReviewAssignment $reviewAssignment): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($reviewAssignment, $data, $request): void {
            $reviewAssignment->loadMissing('submission');

            $attachmentPath = $request->hasFile('attachment')
                ? $request->file('attachment')->store('reviews/'.$reviewAssignment->uuid)
                : $reviewAssignment->review?->attachment;

            $review = $reviewAssignment->review()->updateOrCreate(
                ['review_assignment_id' => $reviewAssignment->id],
                [
                    'comments_for_author' => $data['comments_for_author'] ?? null,
                    'confidential_comments' => $data['confidential_comments'] ?? null,
                    'recommendation' => $data['recommendation'],
                    'attachment' => $attachmentPath,
                    'submitted_at' => now(),
                ],
            );

            $review->scores()->delete();

            foreach ($data['scores'] as $criteria => $score) {
                $review->scores()->create([
                    'criteria' => (string) $criteria,
                    'score' => (int) $score,
                ]);
            }

            $reviewAssignment->update(['status' => 'reviewed']);
            $previousStatus = $reviewAssignment->submission->status;
            $nextStatus = in_array($previousStatus, [SubmissionStatus::AbstractSubmitted, SubmissionStatus::Screening, SubmissionStatus::UnderReview], true) ? SubmissionStatus::UnderReview : $previousStatus;
            $reviewAssignment->submission->update(['status' => $nextStatus]);

            app(WorkflowMailService::class)->authors(
                $reviewAssignment->submission, 'review_result', 'Review completed', [
                    'recommendation' => $review->recommendation->label(),
                    'comments_for_author' => $review->comments_for_author,
                ],
            );

            SubmissionStatusHistory::query()->create([
                'submission_id' => $reviewAssignment->submission_id,
                'changed_by' => $request->user()->id,
                'from_status' => $previousStatus->value,
                'to_status' => $nextStatus->value,
                'notes' => 'Review submitted.',
            ]);
        });

        return back()->with('status', 'Review submitted.');
    }
}
