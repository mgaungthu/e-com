<?php

namespace App\Http\Requests\Admin\Order;

use App\Enums\PaymentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->input('payment_status') === PaymentStatus::Refunded->value
            ? 'orders.refund'
            : 'orders.update';

        return $this->user()?->can($permission) ?? false;
    }

    public function rules(): array
    {
        return [
            'payment_status' => ['required', Rule::enum(PaymentStatus::class)],
        ];
    }
}
