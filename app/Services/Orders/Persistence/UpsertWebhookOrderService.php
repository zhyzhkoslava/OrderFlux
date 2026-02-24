<?php

declare(strict_types=1);

namespace App\Services\Orders\Persistence;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Customer;
use App\Models\DeliveryAddress;
use App\Models\Order;
use App\Models\Restaurant;
use Carbon\CarbonImmutable;

final class UpsertWebhookOrderService
{
    /**
     * @param  array<string, mixed>  $orderPayload
     */
    public function upsert(
        Restaurant $restaurant,
        Customer $customer,
        DeliveryAddress $deliveryAddress,
        CarbonImmutable $requestedAtUtc,
        array $orderPayload,
    ): Order {
        return Order::query()->updateOrCreate(
            [
                'public_id' => (string) $orderPayload['public_id'],
            ],
            [
                'restaurant_id'       => $restaurant->getKey(),
                'customer_id'         => $customer->getKey(),
                'delivery_address_id' => $deliveryAddress->getKey(),
                'scheduled_at'        => $requestedAtUtc,
                'type'                => OrderType::from((string) $orderPayload['type']),
                'status'              => OrderStatus::from((string) ($orderPayload['status'] ?? OrderStatus::NEW->value)),
                'total_cents'         => (int) $orderPayload['total_cents'],
                'delivery_fee_cents'  => (int) $orderPayload['delivery_fee_cents'],
                'currency'            => (string) $orderPayload['currency'],
            ],
        );
    }
}
