<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Customer;
use App\Models\DeliveryAddress;
use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'public_id'           => (string) Str::uuid(),
            'restaurant_id'       => Restaurant::factory(),
            'customer_id'         => Customer::factory(),
            'delivery_address_id' => DeliveryAddress::factory(),
            'scheduled_at'        => now()->addMinutes(30),
            'type'                => Order::TYPE_PREORDER,
            'status'              => Order::STATUS_PENDING,
            'order_amount'        => 2500,
            'delivery_amount'     => 300,
        ];
    }
}
