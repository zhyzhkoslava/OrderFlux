<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_order_crud_flow(): void
    {
        $restaurant = Restaurant::factory()->create();
        $customer = Customer::factory()->create();

        $payload = [
            'restaurant_public_id' => $restaurant->public_id,
            'customer_public_id'   => $customer->public_id,
            'type'                 => Order::TYPE_ASAP,
            'delivery_amount'      => 500,
            'delivery_address'     => [
                'line1'        => 'Delivery Street 5',
                'line2'        => 'Floor 3',
                'city'         => 'Kyiv',
                'region'       => 'Kyiv',
                'postal_code'  => '01001',
                'country_code' => 'UA',
                'instructions' => 'Call on arrival',
            ],
            'items' => [
                [
                    'name'       => 'Margherita',
                    'sku'        => 'PIZZA-1',
                    'quantity'   => 2,
                    'unit_price' => 1000,
                    'modifiers'  => [
                        [
                            'name'       => 'Extra cheese',
                            'quantity'   => 1,
                            'unit_price' => 200,
                        ],
                    ],
                ],
            ],
        ];

        $createResponse = $this->postJson('/api/orders', $payload);
        $createResponse->assertCreated();
        $createResponse->assertJsonMissing(['id']);
        $createResponse->assertJsonPath('data.type', Order::TYPE_ASAP);
        $createResponse->assertJsonPath('data.order_amount', 2200);
        $createResponse->assertJsonPath('data.delivery_amount', 500);
        $createResponse->assertJsonPath('data.items.0.name', 'Margherita');

        $order = Order::query()->firstOrFail();

        $this->getJson('/api/orders')->assertOk();

        $showResponse = $this->getJson('/api/orders/'.$order->public_id);
        $showResponse->assertOk();
        $showResponse->assertJsonPath('data.public_id', $order->public_id);

        $updateResponse = $this->putJson('/api/orders/'.$order->public_id, [
            'status' => Order::STATUS_CONFIRMED,
        ]);
        $updateResponse->assertOk();
        $this->assertDatabaseHas('orders', [
            'id'     => $order->id,
            'status' => Order::STATUS_CONFIRMED,
        ]);

        $this->deleteJson('/api/orders/'.$order->public_id)->assertNoContent();
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    #[Test]
    public function test_order_store_requires_scheduled_at_for_preorder(): void
    {
        $restaurant = Restaurant::factory()->create();
        $customer = Customer::factory()->create();

        $payload = $this->validOrderPayload($restaurant, $customer);
        $payload['type'] = Order::TYPE_PREORDER;
        unset($payload['scheduled_at']);

        $this->postJson('/api/orders', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['scheduled_at']);
    }

    #[Test]
    public function test_order_store_validates_foreign_keys_and_item_fields(): void
    {
        $payload = [
            'restaurant_public_id' => '00000000-0000-0000-0000-000000000000',
            'customer_public_id'   => '00000000-0000-0000-0000-000000000000',
            'type'                 => Order::TYPE_ASAP,
            'delivery_amount'      => -1,
            'delivery_address'     => [
                'line1'        => '',
                'city'         => '',
                'country_code' => 'USA',
            ],
            'items' => [
                [
                    'name'       => '',
                    'quantity'   => 0,
                    'unit_price' => -1,
                    'modifiers'  => [
                        [
                            'name'       => '',
                            'quantity'   => 0,
                            'unit_price' => -1,
                        ],
                    ],
                ],
            ],
        ];

        $this->postJson('/api/orders', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'restaurant_public_id',
                'customer_public_id',
                'delivery_amount',
                'delivery_address.line1',
                'delivery_address.city',
                'delivery_address.country_code',
                'items.0.name',
                'items.0.quantity',
                'items.0.unit_price',
                'items.0.modifiers.0.name',
                'items.0.modifiers.0.quantity',
                'items.0.modifiers.0.unit_price',
            ]);
    }

    #[Test]
    public function test_order_update_allows_clearing_scheduled_at(): void
    {
        $order = Order::factory()->create([
            'scheduled_at' => now()->addHour(),
            'status'       => Order::STATUS_PENDING,
        ]);

        $this->putJson('/api/orders/'.$order->public_id, [
            'scheduled_at' => null,
            'status'       => Order::STATUS_CONFIRMED,
        ])->assertOk();

        $this->assertDatabaseHas('orders', [
            'id'           => $order->id,
            'scheduled_at' => null,
            'status'       => Order::STATUS_CONFIRMED,
        ]);
    }

    #[Test]
    public function test_order_update_validates_status(): void
    {
        $order = Order::factory()->create();

        $this->putJson('/api/orders/'.$order->public_id, [
            'status' => 'UNKNOWN_STATUS',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    #[Test]
    public function test_order_show_returns_not_found_for_unknown_public_id(): void
    {
        $this->getJson('/api/orders/00000000-0000-0000-0000-000000000000')
            ->assertNotFound();
    }

    /**
     * @return array<string, mixed>
     */
    private function validOrderPayload(Restaurant $restaurant, Customer $customer): array
    {
        return [
            'restaurant_public_id' => $restaurant->public_id,
            'customer_public_id'   => $customer->public_id,
            'type'                 => Order::TYPE_ASAP,
            'scheduled_at'         => now()->addHour()->toIso8601String(),
            'delivery_amount'      => 500,
            'delivery_address'     => [
                'line1'        => 'Delivery Street 5',
                'line2'        => 'Floor 3',
                'city'         => 'Kyiv',
                'region'       => 'Kyiv',
                'postal_code'  => '01001',
                'country_code' => 'UA',
                'instructions' => 'Call on arrival',
            ],
            'items' => [
                [
                    'name'       => 'Margherita',
                    'sku'        => 'PIZZA-1',
                    'quantity'   => 2,
                    'unit_price' => 1000,
                    'modifiers'  => [
                        [
                            'name'       => 'Extra cheese',
                            'quantity'   => 1,
                            'unit_price' => 200,
                        ],
                    ],
                ],
            ],
        ];
    }
}
