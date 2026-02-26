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

final class OrderServiceUpdateTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function update_returns_404_when_order_not_found(): void
    {
        $service = new OrderService;

        $result = $service->update([
            'order_public_id' => 'missing-order',
            'order'           => [
                'restaurant_public_id'  => 'missing-restaurant',
                'request_delivery_time' => '2026-02-26 12:00:00',
                'type'                  => 'ASAP',
            ],
            'customer' => [
                'public_id' => 'missing-customer',
            ],
            'delivery_address' => [
                'public_id' => 'missing-address',
            ],
            'order_item' => [],
        ]);

        self::assertSame(404, $result['statusCode']);
        self::assertSame('Order not found.', $result['payload']['message']);
    }

    #[Test]
    public function update_replaces_items_and_removes_deleted_modifiers(): void
    {
        $restaurant = Restaurant::factory()->create([
            'public_id' => 'rest-update-1',
            'currency'  => 'USD',
        ]);
        $customer = Customer::factory()->create([
            'public_id'     => 'customer-update-1',
            'restaurant_id' => $restaurant->getKey(),
        ]);
        $deliveryAddress = DeliveryAddress::factory()->create([
            'public_id'   => 'address-update-1',
            'customer_id' => $customer->getKey(),
        ]);
        $order = Order::factory()->create([
            'public_id'           => 'order-update-1',
            'restaurant_id'       => $restaurant->getKey(),
            'customer_id'         => $customer->getKey(),
            'delivery_address_id' => $deliveryAddress->getKey(),
            'status'              => OrderStatus::NEW->value,
        ]);
        $oldItem = OrderItem::factory()->create([
            'public_id' => 'item-old-1',
            'order_id'  => $order->getKey(),
        ]);
        OrderItemModifier::factory()->create([
            'public_id'     => 'modifier-old-1',
            'order_item_id' => $oldItem->getKey(),
        ]);

        $service = new OrderService;
        $result = $service->update([
            'order_public_id' => 'order-update-1',
            'order'           => [
                'restaurant_public_id'  => 'rest-update-1',
                'request_delivery_time' => '2026-02-26 15:00:00',
                'type'                  => 'PREORDER',
                'status'                => 'ACCEPTED',
            ],
            'customer' => [
                'public_id' => 'customer-update-1',
            ],
            'delivery_address' => [
                'public_id' => 'address-update-1',
            ],
            'order_item' => [
                [
                    'public_id'   => 'item-new-1',
                    'sku'         => 'sku-new',
                    'quantity'    => 3,
                    'price_cents' => 900,
                    'modifiers'   => [
                        [
                            'public_id'   => 'modifier-new-1',
                            'type'        => 'remove',
                            'quantity'    => 1,
                            'price_cents' => 30,
                        ],
                    ],
                ],
            ],
        ]);

        self::assertSame(200, $result['statusCode']);
        self::assertSame('order-update-1', $result['payload']['order_public_id']);

        $order->refresh();
        self::assertSame(OrderStatus::ACCEPTED->value, $order->getRawOriginal('status'));
        self::assertDatabaseMissing('order_items', ['public_id' => 'item-old-1']);
        self::assertDatabaseMissing('order_item_modifiers', ['public_id' => 'modifier-old-1']);
        self::assertDatabaseHas('order_items', ['public_id' => 'item-new-1', 'order_id' => $order->getKey()]);
        self::assertDatabaseHas('order_item_modifiers', ['public_id' => 'modifier-new-1']);
    }
}
