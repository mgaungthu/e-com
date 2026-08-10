<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PaymentMethodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'account_name' => $this->account_name,
            'account_number' => $this->account_number,

            'qr_image_url' => $this->qr_image_path
                ? Storage::disk('public')->url($this->qr_image_path)
                : null,

            'instructions' => $this->instructions,
            'is_active' => (bool) $this->is_active,
            'sort_order' => (int) $this->sort_order,
        ];
    }
}
