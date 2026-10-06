<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class DeleteAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('confirmation'))) {
            $this->merge(['confirmation' => trim($this->input('confirmation'))]);
        }
    }

    public function rules(): array
    {
        return [
            'confirmation' => ['required', 'string', 'in:confirm'],
            'password' => ['nullable', 'string', 'required_without_all:google_id_token,apple_identity_token', 'prohibits:google_id_token,apple_identity_token'],
            'google_id_token' => ['nullable', 'string', 'required_without_all:password,apple_identity_token', 'prohibits:password,apple_identity_token'],
            'apple_identity_token' => ['nullable', 'string', 'max:16384', 'required_without_all:password,google_id_token', 'prohibits:password,google_id_token'],
        ];
    }
}
