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
            'password' => ['nullable', 'string', 'required_without:google_id_token', 'prohibits:google_id_token'],
            'google_id_token' => ['nullable', 'string', 'required_without:password', 'prohibits:password'],
        ];
    }
}
