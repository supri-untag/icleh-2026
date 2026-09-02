<?php

namespace App\Http\Requests\Participant;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
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
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'whatsapp' => ['required', 'string', 'max:40'],
            'institution' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:120'],
            'participant_type' => ['nullable', 'in:internal_student,general,participant,presenter'],
            'attendance_mode' => ['nullable', 'in:online,offline'],
            'status_proof_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ];
    }
}
