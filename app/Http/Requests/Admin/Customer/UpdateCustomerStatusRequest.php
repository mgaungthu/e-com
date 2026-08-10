<?php

namespace App\Http\Requests\Admin\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('customers.block') ?? false;
    }

    public function rules(): array
    {
        return ['status' => ['required', Rule::in(['active', 'inactive', 'blocked', 'pending'])]];
    }
}
