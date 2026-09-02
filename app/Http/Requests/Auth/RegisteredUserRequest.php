<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisteredUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'whatsapp' => ['required', 'string', 'max:40'],
            'institution' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:120'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'consent' => ['accepted'],
        ];
    }
}
