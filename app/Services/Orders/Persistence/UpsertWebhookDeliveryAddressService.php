<?php

declare(strict_types=1);

namespace App\Services\Orders\Persistence;

use App\Models\Customer;
use App\Models\DeliveryAddress;

final class UpsertWebhookDeliveryAddressService
{
    /**
     * @param  array<string, mixed>  $addressPayload
     */
    public function upsert(Customer $customer, array $addressPayload): DeliveryAddress
    {
        return DeliveryAddress::query()->updateOrCreate(
            [
                'public_id' => (string) $addressPayload['public_id'],
            ],
            [
                'customer_id' => $customer->getKey(),
                'city'        => (string) $addressPayload['city'],
                'street'      => (string) $addressPayload['street'],
                'building'    => (string) $addressPayload['building'],
                'apartment'   => $addressPayload['apartment'] ?? null,
                'comment'     => $addressPayload['comment'] ?? null,
            ],
        );
    }
}
