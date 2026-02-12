<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DeliveryAddress;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DeliveryAddress>
 */
class DeliveryAddressFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'public_id'    => (string) Str::uuid(),
            'line1'        => fake()->streetAddress(),
            'line2'        => fake()->optional()->word(),
            'city'         => fake()->city(),
            'region'       => fake()->word(),
            'postal_code'  => fake()->postcode(),
            'country_code' => fake()->countryCode(),
            'instructions' => fake()->optional()->sentence(),
        ];
    }
}
