<?php

namespace App\Http\Requests\Participant;

use App\Models\RegistrationFee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistrationRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $feeId = $this->input('registration_fee_id');
        $fee = is_scalar($feeId) && filter_var($feeId, FILTER_VALIDATE_INT) !== false
            ? RegistrationFee::query()->active()->find($feeId)
            : null;

        $this->merge(['participant_type' => $fee?->participant_type]);
    }

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
            'registration_fee_id' => ['bail', 'required', 'integer', Rule::exists('registration_fees', 'id')->where('active', true)],
            'country_id' => ['required', 'integer', Rule::exists('countries', 'id')->where('active', true)],
            'participant_type' => ['required', 'in:internal_student,general,participant,presenter'],
            'attendance_mode' => ['nullable', 'required_if:participant_type,presenter', 'in:online,offline'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
