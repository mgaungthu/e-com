<?php

namespace App\Http\Requests\Api\V1\Address;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => [
                'sometimes',
                'string',
                Rule::in(['home', 'office', 'other']),
            ],

            'label' => [
                'nullable',
                'string',
                'max:100',
            ],

            'recipient_name' => [
                'required',
                'string',
                'max:255',
            ],

            'phone' => [
                'required',
                'string',
                'max:30',
            ],

            'alternate_phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'address_line_one' => [
                'required',
                'string',
                'max:255',
            ],

            'address_line_two' => [
                'nullable',
                'string',
                'max:255',
            ],

            'building' => [
                'nullable',
                'string',
                'max:255',
            ],

            'floor' => [
                'nullable',
                'string',
                'max:50',
            ],

            'unit' => [
                'nullable',
                'string',
                'max:50',
            ],

            'landmark' => [
                'nullable',
                'string',
                'max:255',
            ],

            'township' => [
                'nullable',
                'string',
                'max:150',
            ],

            'city' => [
                'required',
                'string',
                'max:150',
            ],

            'state' => [
                'nullable',
                'string',
                'max:150',
            ],

            'postal_code' => [
                'nullable',
                'string',
                'max:30',
            ],

            'country_code' => [
                'sometimes',
                'string',
                'size:2',
            ],

            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],

            'delivery_instruction' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'is_default_shipping' => [
                'sometimes',
                'boolean',
            ],

            'is_default_billing' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}
