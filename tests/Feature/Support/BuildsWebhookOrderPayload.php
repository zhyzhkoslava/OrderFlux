<?php

declare(strict_types=1);

namespace Tests\Feature\Support;

use App\Enums\Currency;
use App\Enums\OrderStatus;
use App\Enums\OrderType;

trait BuildsWebhookOrderPayload
{
    /**
     * @return array<string, mixed>
     */
    private function validPayload(string $restaurantPublicId): array
    {
        return [
            'order' => [
                'public_id'            => 'ord-001',
                'restaurant_public_id' => $restaurantPublicId,
                'requested_at'         => '2026-02-23T15:00:00Z',
                'type'                 => OrderType::ASAP->value,
                'total_cents'          => 2500,
                'delivery_fee_cents'   => 300,
                'currency'             => Currency::USD->value,
                'status'               => OrderStatus::NEW->value,
            ],
            'customer' => [
                'public_id' => 'cust-001',
                'name'      => 'Jane Doe',
                'phone'     => '+15550100001',
                'email'     => 'jane@example.com',
            ],
            'delivery_address' => [
                'public_id' => 'addr-001',
                'city'      => 'New York',
                'street'    => 'Main St',
                'building'  => '10',
                'apartment' => '2A',
                'comment'   => 'Ring the bell',
            ],
            'items' => [
                [
                    'public_id'   => 'item-001',
                    'sku'         => 'pizza_margherita',
                    'qty'         => 2,
                    'price_cents' => 1100,
                    'currency'    => Currency::USD->value,
                    'modifiers'   => [
                        [
                            'public_id'   => 'mod-001',
                            'type'        => 'add',
                            'quantity'    => 1,
                            'price_cents' => 300,
                            'currency'    => Currency::USD->value,
                        ],
                    ],
                ],
            ],
        ];
    }
}
