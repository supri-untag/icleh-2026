<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SubmissionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewRequest;
use App\Models\ReviewAssignment;
use App\Models\SubmissionStatusHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function store(ReviewRequest $request, ReviewAssignment $reviewAssignment): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($reviewAssignment, $data, $request): void {
            $reviewAssignment->loadMissing('submission');

            $attachmentPath = $request->hasFile('attachment')
                ? $request->file('attachment')->store('reviews/'.$reviewAssignment->uuid)
                : null;

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
            $reviewAssignment->submission->update(['status' => SubmissionStatus::UnderReview]);

            SubmissionStatusHistory::query()->create([
                'submission_id' => $reviewAssignment->submission_id,
                'changed_by' => $request->user()->id,
                'from_status' => null,
                'to_status' => SubmissionStatus::UnderReview->value,
                'notes' => 'Review submitted.',
            ]);
        });

        return back()->with('status', 'Review submitted.');
    }
}
