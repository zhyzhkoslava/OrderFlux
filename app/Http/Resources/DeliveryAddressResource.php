<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\DeliveryAddress;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @mixin DeliveryAddress
 */
class DeliveryAddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'public_id'    => $this->public_id,
            'line1'        => $this->line1,
            'line2'        => $this->line2,
            'city'         => $this->city,
            'region'       => $this->region,
            'postal_code'  => $this->postal_code,
            'country_code' => $this->country_code,
            'instructions' => $this->instructions,
            'created_at'   => optional($this->created_at)?->toISOString(),
            'updated_at'   => optional($this->updated_at)?->toISOString(),
        ];
    }
}
