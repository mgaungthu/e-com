<?php

namespace App\Http\Requests\Admin\Order;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->input('status') === OrderStatus::Cancelled->value
            ? 'orders.cancel'
            : 'orders.update';

        return $this->user()?->can($permission) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(OrderStatus::class)],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
