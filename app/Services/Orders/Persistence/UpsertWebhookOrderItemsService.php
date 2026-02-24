<?php

declare(strict_types=1);

namespace App\Services\Orders\Persistence;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemModifier;

final class UpsertWebhookOrderItemsService
{
    /**
     * @param  array<int, mixed>  $itemsPayload
     */
    public function upsert(Order $order, array $itemsPayload): void
    {
        foreach ($itemsPayload as $itemPayload) {
            $itemPayload = (array) $itemPayload;

            $orderItem = OrderItem::query()->updateOrCreate(
                [
                    'public_id' => (string) $itemPayload['public_id'],
                ],
                [
                    'order_id'    => $order->getKey(),
                    'sku'         => (string) $itemPayload['sku'],
                    'qty'         => (int) $itemPayload['qty'],
                    'price_cents' => (int) $itemPayload['price_cents'],
                    'currency'    => (string) $itemPayload['currency'],
                ],
            );

            $this->upsertModifiers($orderItem, (array) ($itemPayload['modifiers'] ?? []));
        }
    }

    /**
     * @param  array<int, mixed>  $modifiersPayload
     */
    private function upsertModifiers(OrderItem $orderItem, array $modifiersPayload): void
    {
        foreach ($modifiersPayload as $modifierPayload) {
            $modifierPayload = (array) $modifierPayload;

            OrderItemModifier::query()->updateOrCreate(
                [
                    'public_id' => (string) $modifierPayload['public_id'],
                ],
                [
                    'order_item_id' => $orderItem->getKey(),
                    'type'          => (string) $modifierPayload['type'],
                    'quantity'      => (int) $modifierPayload['quantity'],
                    'price_cents'   => (int) $modifierPayload['price_cents'],
                    'currency'      => (string) $modifierPayload['currency'],
                ],
            );
        }
    }
}
