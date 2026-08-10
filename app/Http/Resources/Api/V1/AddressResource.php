<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'label' => $this->label,
            'recipient_name' => $this->recipient_name,
            'phone' => $this->phone,
            'alternate_phone' => $this->alternate_phone,
            'address_line_one' => $this->address_line_one,
            'address_line_two' => $this->address_line_two,
            'building' => $this->building,
            'floor' => $this->floor,
            'unit' => $this->unit,
            'landmark' => $this->landmark,
            'township' => $this->township,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'country_code' => $this->country_code,

            'latitude' => $this->latitude !== null
                ? (float) $this->latitude
                : null,

            'longitude' => $this->longitude !== null
                ? (float) $this->longitude
                : null,

            'delivery_instruction' => $this->delivery_instruction,

            'is_default_shipping' => (bool) $this->is_default_shipping,
            'is_default_billing' => (bool) $this->is_default_billing,

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
