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
}
