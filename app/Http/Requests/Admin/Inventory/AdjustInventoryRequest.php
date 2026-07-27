<?php

namespace App\Http\Requests\Admin\Inventory;

use Illuminate\Validation\Validator;

class AdjustInventoryRequest extends InventoryRequest
{
    public function authorize(): bool
    {
        return true;

        // Permission system ချိတ်ပြီးရင်:
        // return $this->user()?->can(
        //     'inventory.adjust',
        // ) ?? false;
    }

    public function rules(): array
    {
        return $this->commonRules();
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $product = $this->route('product');
                $type = $this->string('type')->toString();
                $quantity = $this->integer('quantity');

                if (
                    $type === 'subtraction' &&
                    $quantity > $product->stock_quantity
                ) {
                    $validator->errors()->add(
                        'quantity',
                        'The removal quantity cannot exceed the current stock.',
                    );
                }
            },
        ];
    }
}