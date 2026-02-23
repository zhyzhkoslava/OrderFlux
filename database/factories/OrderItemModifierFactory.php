<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Currency;
use App\Enums\OrderItemModifierType;
use App\Models\OrderItem;
use App\Models\OrderItemModifier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItemModifier>
 */
final class OrderItemModifierFactory extends Factory
{
    protected $model = OrderItemModifier::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'public_id'     => (string) fake()->uuid(),
            'order_item_id' => OrderItem::factory(),
            'type'          => fake()->randomElement(OrderItemModifierType::cases()),
            'quantity'      => fake()->numberBetween(1, 3),
            'price_cents'   => fake()->numberBetween(0, 3000),
            'currency'      => fake()->randomElement(Currency::cases()),
        ];
    }
}
