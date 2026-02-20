<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
final class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'public_id'     => (string) fake()->uuid(),
            'restaurant_id' => Restaurant::factory(),
            'name'          => fake()->name(),
            'email'         => fake()->safeEmail(),
            'phone'         => fake()->e164PhoneNumber(),
        ];
    }
}
