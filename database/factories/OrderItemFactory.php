<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 3);
        $unitPrice = fake()->numberBetween(500, 2000);

        return [
            'order_id'    => Order::factory(),
            'name'        => fake()->words(2, true),
            'sku'         => fake()->optional()->bothify('SKU-####'),
            'quantity'    => $quantity,
            'unit_price'  => $unitPrice,
            'total_price' => $quantity * $unitPrice,
        ];
    }
}
