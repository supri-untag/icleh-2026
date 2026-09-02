<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\RegistrationStatus;
use App\Enums\SubmissionStatus;
use App\Models\Conference;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Submission;

class AdminDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function summary(Conference $conference): array
    {
        $registrationQuery = Registration::query()->whereBelongsTo($conference);
        $submissionQuery = Submission::query()->whereBelongsTo($conference);

        return [
            'total_registered' => (clone $registrationQuery)->count(),
            'total_verified' => (clone $registrationQuery)->whereIn('status', [
                RegistrationStatus::PaymentVerified->value,
                RegistrationStatus::Confirmed->value,
                RegistrationStatus::Attended->value,
                RegistrationStatus::Completed->value,
            ])->count(),
            'presenters' => (clone $registrationQuery)->where('participant_type', 'presenter')->count(),
            'participants' => (clone $registrationQuery)->where('participant_type', '!=', 'presenter')->count(),
            'online_presenters' => (clone $registrationQuery)->where('participant_type', 'presenter')->where('attendance_mode', 'online')->count(),
            'offline_presenters' => (clone $registrationQuery)->where('participant_type', 'presenter')->where('attendance_mode', 'offline')->count(),
            'pending_payments' => Payment::query()
                ->whereHas('registration', fn ($query) => $query->whereBelongsTo($conference))
                ->where('status', PaymentStatus::Submitted->value)
                ->count(),
            'revenue' => Payment::query()
                ->whereHas('registration', fn ($query) => $query->whereBelongsTo($conference))
                ->where('status', PaymentStatus::Verified->value)
                ->sum('amount'),
            'abstracts_submitted' => (clone $submissionQuery)->whereNot('status', SubmissionStatus::Draft->value)->count(),
            'under_review' => (clone $submissionQuery)->where('status', SubmissionStatus::UnderReview->value)->count(),
            'accepted' => (clone $submissionQuery)->whereIn('status', [
                SubmissionStatus::AbstractAccepted->value,
                SubmissionStatus::LoaIssued->value,
                SubmissionStatus::FullPaperSubmitted->value,
                SubmissionStatus::FullPaperAccepted->value,
                SubmissionStatus::Scheduled->value,
                SubmissionStatus::Presented->value,
                SubmissionStatus::Completed->value,
            ])->count(),
            'rejected' => (clone $submissionQuery)->where('status', SubmissionStatus::AbstractRejected->value)->count(),
            'full_papers' => (clone $submissionQuery)->whereIn('status', [
                SubmissionStatus::FullPaperSubmitted->value,
                SubmissionStatus::FullPaperReview->value,
                SubmissionStatus::FullPaperAccepted->value,
            ])->count(),
            'recent_registrations' => Registration::query()
                ->whereBelongsTo($conference)
                ->with(['user:id,name,email', 'fee:id,name,amount,currency'])
                ->latest()
                ->limit(5)
                ->get(),
            'recent_submissions' => Submission::query()
                ->whereBelongsTo($conference)
                ->with(['user:id,name,email', 'topic:id,title'])
                ->latest()
                ->limit(5)
                ->get(),
        ];
    }
}
