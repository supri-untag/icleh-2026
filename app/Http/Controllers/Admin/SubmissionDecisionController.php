<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SubmissionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SubmissionDecisionRequest;
use App\Models\Submission;
use App\Services\SubmissionService;
use Illuminate\Http\JsonResponse;

class SubmissionDecisionController extends Controller
{
    public function __construct(private SubmissionService $submissionService) {}

    public function update(SubmissionDecisionRequest $request, Submission $submission): JsonResponse
    {
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
