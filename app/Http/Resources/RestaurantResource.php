<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @mixin Restaurant
 */
class RestaurantResource extends JsonResource
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
            'public_id' => $this->public_id,
            'name'      => $this->name,
            'slug'      => $this->slug,
            'timezone'  => $this->timezone,
            'currency'  => $this->currency,
            'is_active' => $this->is_active,
            'address'   => [
                'line1'        => $this->address_line1,
                'line2'        => $this->address_line2,
                'city'         => $this->address_city,
                'region'       => $this->address_region,
                'postal_code'  => $this->address_postal_code,
                'country_code' => $this->address_country_code,
            ],
            'created_at' => optional($this->created_at)?->toISOString(),
            'updated_at' => optional($this->updated_at)?->toISOString(),
        ];
    }
}
