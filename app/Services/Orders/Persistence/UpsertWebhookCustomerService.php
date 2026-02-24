<?php

declare(strict_types=1);

namespace App\Services\Orders\Persistence;

use App\Models\Customer;
use App\Models\Restaurant;

final class UpsertWebhookCustomerService
{
    /**
     * @param  array<string, mixed>  $customerPayload
     */
    public function upsert(Restaurant $restaurant, array $customerPayload): Customer
    {
        return Customer::query()->updateOrCreate(
            [
                'restaurant_id' => $restaurant->getKey(),
                'phone'         => (string) $customerPayload['phone'],
            ],
            [
                'public_id' => (string) $customerPayload['public_id'],
                'name'      => (string) $customerPayload['name'],
                'email'     => $customerPayload['email'] ?? null,
            ],
        );
    }
}
