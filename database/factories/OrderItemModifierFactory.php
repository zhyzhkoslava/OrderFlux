<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\OrderItemModifier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItemModifier>
 */
class OrderItemModifierFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 2);
        $unitPrice = fake()->numberBetween(100, 500);

        return [
            'order_item_id' => OrderItem::factory(),
            'name'          => fake()->words(2, true),
            'quantity'      => $quantity,
            'unit_price'    => $unitPrice,
            'total_price'   => $quantity * $unitPrice,
        ];
    }
}
