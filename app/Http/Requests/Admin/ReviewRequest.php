<?php

namespace App\Http\Requests\Admin;

use App\Enums\ReviewRecommendation;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole([
            UserRole::SuperAdmin,
            UserRole::Admin,
            UserRole::Reviewer,
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
            'comments_for_author' => ['nullable', 'string', 'max:5000'],
            'confidential_comments' => ['nullable', 'string', 'max:5000'],
            'recommendation' => ['required', Rule::enum(ReviewRecommendation::class)],
            'scores' => ['required', 'array'],
            'scores.*' => ['required', 'integer', 'min:1', 'max:5'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ];
    }
}
