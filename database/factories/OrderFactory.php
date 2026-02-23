<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Currency;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
final class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'public_id'           => (string) fake()->uuid(),
            'restaurant_id'       => Restaurant::factory(),
            'customer_id'         => Customer::factory(),
            'delivery_address_id' => null,
            'scheduled_at'        => fake()->dateTimeBetween('now', '+2 days'),
            'type'                => fake()->randomElement(OrderType::cases()),
            'status'              => fake()->randomElement(OrderStatus::cases()),
            'total_cents'         => fake()->numberBetween(500, 50000),
            'delivery_fee_cents'  => fake()->numberBetween(0, 10000),
            'currency'            => fake()->randomElement(Currency::cases()),
        ];
    }
}
