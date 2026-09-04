<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'size:4',
                'regex:/^\d{4}$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Verification code is required.',
            'code.size' => 'Verification code must be 4 digits.',
            'code.regex' => 'Verification code must contain only numbers.',
        ];
    }
}