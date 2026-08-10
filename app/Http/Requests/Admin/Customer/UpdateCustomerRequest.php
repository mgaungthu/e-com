<?php

namespace App\Http\Requests\Admin\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('customers.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'display_name' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($this->route('customer'))],
            'admin_note' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
