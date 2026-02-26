<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Orders;

use App\Models\Customer;
use App\Models\DeliveryAddress;
use App\Models\Order;
use App\Models\Restaurant;
use App\Services\Orders\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class OrderServiceDeleteTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function delete_returns_404_for_unknown_order(): void
    {
        $service = new OrderService;

        $result = $service->delete([
            'order_public_id' => 'missing-order',
        ]);

        self::assertSame(404, $result['statusCode']);
        self::assertSame('Order not found.', $result['payload']['message']);
    }

    #[Test]
    public function delete_removes_existing_order(): void
    {
        $restaurant = Restaurant::factory()->create();
        $customer = Customer::factory()->create([
            'restaurant_id' => $restaurant->getKey(),
        ]);
        $deliveryAddress = DeliveryAddress::factory()->create([
            'customer_id' => $customer->getKey(),
        ]);
        Order::factory()->create([
            'public_id'           => 'order-delete-1',
            'restaurant_id'       => $restaurant->getKey(),
            'customer_id'         => $customer->getKey(),
            'delivery_address_id' => $deliveryAddress->getKey(),
        ]);

        $service = new OrderService;
        $result = $service->delete([
            'order_public_id' => 'order-delete-1',
        ]);

        self::assertSame(200, $result['statusCode']);
        self::assertSame('deleted', $result['payload']['status']);
        self::assertDatabaseMissing('orders', ['public_id' => 'order-delete-1']);
    }
}
