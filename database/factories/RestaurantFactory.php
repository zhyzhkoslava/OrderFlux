<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Restaurant>
 */
class RestaurantFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'public_id'            => (string) Str::uuid(),
            'name'                 => $name,
            'slug'                 => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'address_line1'        => fake()->streetAddress(),
            'address_line2'        => fake()->optional()->word(),
            'address_city'         => fake()->city(),
            'address_region'       => fake()->word(),
            'address_postal_code'  => fake()->postcode(),
            'address_country_code' => fake()->countryCode(),
            'timezone'             => fake()->timezone(),
            'currency'             => 'USD',
            'is_active'            => true,
        ];
    }
}
