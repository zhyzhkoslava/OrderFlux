<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Orders;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\DeliveryAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemModifier;
use App\Models\Restaurant;
use App\Services\Orders\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class OrderServiceCreateTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function create_persists_order_items_and_modifiers(): void
    {
        $restaurant = Restaurant::factory()->create([
            'public_id' => 'rest-create-1',
            'currency'  => 'USD',
        ]);

        $service = new OrderService;

        $result = $service->create([
            'order' => [
                'public_id'             => 'order-create-1',
                'restaurant_public_id'  => 'rest-create-1',
                'request_delivery_time' => '2026-02-26 12:00:00',
                'type'                  => 'ASAP',
                'status'                => 'NEW',
                'total_cents'           => 1500,
                'delivery_fee_cents'    => 200,
            ],
            'customer' => [
                'public_id' => 'customer-create-1',
                'name'      => 'John Doe',
                'phone'     => '+15550000001',
                'email'     => 'john@example.com',
            ],
            'delivery_address' => [
                'city'      => 'New York',
                'street'    => '5th Ave',
                'building'  => '10',
                'apartment' => '2A',
                'comment'   => 'Ring bell',
            ],
            'order_item' => [
                [
                    'public_id'   => 'item-create-1',
                    'sku'         => 'sku-1',
                    'quantity'    => 2,
                    'price_cents' => 600,
                    'modifiers'   => [
                        [
                            'public_id'   => 'modifier-create-1',
                            'type'        => 'add',
                            'quantity'    => 1,
                            'price_cents' => 50,
                        ],
                    ],
                ],
            ],
        ]);

        self::assertSame(201, $result['statusCode']);
        self::assertSame('order-create-1', $result['payload']['order_public_id']);

        $order = Order::query()->where('public_id', 'order-create-1')->first();
        self::assertNotNull($order);
        self::assertSame(OrderStatus::NEW->value, $order->getRawOriginal('status'));

        $customer = Customer::query()->where('public_id', 'customer-create-1')->first();
        self::assertNotNull($customer);
        self::assertSame($restaurant->getKey(), $customer->restaurant_id);

        $deliveryAddress = DeliveryAddress::query()->where('customer_id', $customer->getKey())->first();
        self::assertNotNull($deliveryAddress);
        self::assertSame('New York', $deliveryAddress->city);

        $item = OrderItem::query()->where('public_id', 'item-create-1')->first();
        self::assertNotNull($item);
        self::assertSame($order->getKey(), $item->order_id);
        self::assertSame(2, (int) $item->quantity);

        $modifier = OrderItemModifier::query()->where('public_id', 'modifier-create-1')->first();
        self::assertNotNull($modifier);
        self::assertSame($item->getKey(), $modifier->order_item_id);
    }

    #[Test]
    public function create_returns_existing_order_when_public_id_already_exists(): void
    {
        $restaurant = Restaurant::factory()->create([
            'public_id' => 'rest-create-2',
            'currency'  => 'USD',
        ]);
        $customer = Customer::factory()->create([
            'public_id'     => 'customer-create-2',
            'restaurant_id' => $restaurant->getKey(),
        ]);
        $deliveryAddress = DeliveryAddress::factory()->create([
            'customer_id' => $customer->getKey(),
        ]);
        Order::factory()->create([
            'public_id'           => 'order-create-2',
            'restaurant_id'       => $restaurant->getKey(),
            'customer_id'         => $customer->getKey(),
            'delivery_address_id' => $deliveryAddress->getKey(),
            'status'              => OrderStatus::ACCEPTED->value,
        ]);

        $service = new OrderService;
        $result = $service->create([
            'order' => [
                'public_id'             => 'order-create-2',
                'restaurant_public_id'  => 'rest-create-2',
                'request_delivery_time' => '2026-02-26 12:00:00',
                'type'                  => 'ASAP',
            ],
            'customer' => [
                'public_id' => 'customer-create-2',
            ],
            'delivery_address' => [
                'public_id' => $deliveryAddress->public_id,
            ],
            'order_item' => [],
        ]);

        self::assertSame(200, $result['statusCode']);
        self::assertSame('order-create-2', $result['payload']['order_public_id']);
        self::assertSame(1, Order::query()->where('public_id', 'order-create-2')->count());
    }
}
