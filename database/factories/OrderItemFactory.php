<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Currency;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
final class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'public_id'   => (string) fake()->uuid(),
            'order_id'    => Order::factory(),
            'sku'         => fake()->bothify('sku_####_??'),
            'qty'         => fake()->numberBetween(1, 5),
            'price_cents' => fake()->numberBetween(100, 10000),
            'currency'    => fake()->randomElement(Currency::cases()),
        ];
    }
}
