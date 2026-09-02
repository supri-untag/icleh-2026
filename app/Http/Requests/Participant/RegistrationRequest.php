<?php

namespace App\Http\Requests\Participant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'registration_fee_id' => ['required', Rule::exists('registration_fees', 'id')->where('active', true)],
            'participant_type' => ['required', 'in:internal_student,general,participant,presenter'],
            'attendance_mode' => ['nullable', 'required_if:participant_type,presenter', 'in:online,offline'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
