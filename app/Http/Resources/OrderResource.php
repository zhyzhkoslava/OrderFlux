<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @mixin Order
 */
class OrderResource extends JsonResource
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
            'public_id'        => $this->public_id,
            'type'             => $this->type,
            'status'           => $this->status,
            'scheduled_at'     => optional($this->scheduled_at)?->toISOString(),
            'order_amount'     => $this->order_amount,
            'delivery_amount'  => $this->delivery_amount,
            'restaurant'       => new RestaurantResource($this->whenLoaded('restaurant')),
            'customer'         => new CustomerResource($this->whenLoaded('customer')),
            'delivery_address' => new DeliveryAddressResource($this->whenLoaded('deliveryAddress')),
            'items'            => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at'       => optional($this->created_at)?->toISOString(),
            'updated_at'       => optional($this->updated_at)?->toISOString(),
        ];
    }
}
