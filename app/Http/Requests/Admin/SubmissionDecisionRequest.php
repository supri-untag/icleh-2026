<?php

namespace App\Http\Requests\Admin;

use App\Enums\SubmissionStatus;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmissionDecisionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole([
            UserRole::SuperAdmin,
            UserRole::Admin,
            UserRole::ScientificCommittee,
        ]) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                SubmissionStatus::Screening->value,
                SubmissionStatus::UnderReview->value,
                SubmissionStatus::RevisionRequired->value,
                SubmissionStatus::AbstractAccepted->value,
                SubmissionStatus::AbstractRejected->value,
            ])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
