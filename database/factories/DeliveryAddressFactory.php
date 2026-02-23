<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Customer;
use App\Models\DeliveryAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeliveryAddress>
 */
final class DeliveryAddressFactory extends Factory
{
    protected $model = DeliveryAddress::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'public_id'   => (string) fake()->uuid(),
            'customer_id' => Customer::factory(),
            'city'        => fake()->city(),
            'street'      => fake()->streetName(),
            'building'    => fake()->buildingNumber(),
            'apartment'   => fake()->optional()->numerify('##'),
            'comment'     => fake()->optional()->sentence(4),
        ];
    }
}
