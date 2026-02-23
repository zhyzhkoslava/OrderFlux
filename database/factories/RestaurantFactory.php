<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Currency;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Restaurant>
 */
final class RestaurantFactory extends Factory
{
    protected $model = Restaurant::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'public_id' => (string) fake()->uuid(),
            'name'      => fake()->company(),
            'slug'      => fake()->unique()->slug(),
            'address'   => fake()->streetAddress(),
            'timezone'  => fake()->timezone(),
            'currency'  => fake()->randomElement(Currency::cases()),
            'is_active' => true,
        ];
    }
}
