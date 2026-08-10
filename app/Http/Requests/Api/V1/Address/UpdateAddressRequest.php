<?php

namespace App\Http\Requests\Api\V1\Address;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAddressRequest extends FormRequest
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
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],

            'recipient_name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'phone' => [
                'sometimes',
                'required',
                'string',
                'max:30',
            ],

            'alternate_phone' => [
                'sometimes',
                'nullable',
                'string',
                'max:30',
            ],

            'address_line_one' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'address_line_two' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'building' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'floor' => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
            ],

            'unit' => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
            ],

            'landmark' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'township' => [
                'sometimes',
                'nullable',
                'string',
                'max:150',
            ],

            'city' => [
                'sometimes',
                'required',
                'string',
                'max:150',
            ],

            'state' => [
                'sometimes',
                'nullable',
                'string',
                'max:150',
            ],

            'postal_code' => [
                'sometimes',
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
                'sometimes',
                'nullable',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'sometimes',
                'nullable',
                'numeric',
                'between:-180,180',
            ],

            'delivery_instruction' => [
                'sometimes',
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
