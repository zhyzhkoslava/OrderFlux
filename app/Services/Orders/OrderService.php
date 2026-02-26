<?php

declare(strict_types=1);

namespace App\Services\Orders;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Customer;
use App\Models\DeliveryAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemModifier;
use App\Models\Restaurant;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class OrderService
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array{payload: array<string, mixed>, statusCode: int}
     */
    public function create(array $payload): array
    {
        $orderPayload = $payload['order'] ?? [];
        $orderPublicId = $orderPayload['public_id'] ?? '';
        if ($orderPublicId !== '') {
            $existingOrder = Order::query()->where('public_id', $orderPublicId)->first();
            if ($existingOrder !== null) {
                return $this->successResponse($existingOrder, 200);
            }
        }

        try {
            $order = DB::transaction(fn (): Order => $this->createOrder($payload));
        } catch (InvalidArgumentException $invalidArgumentException) {
            return [
                'payload' => [
                    'message' => $invalidArgumentException->getMessage(),
                ],
                'statusCode' => 422,
            ];
        }

        return $this->successResponse($order, 201);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{payload: array<string, mixed>, statusCode: int}
     */
    public function update(array $payload): array
    {
        $orderPublicId = $payload['order_public_id'] ?? '';
        if ($orderPublicId === '') {
            return [
                'payload' => [
                    'message' => 'Order public id is required.',
                ],
                'statusCode' => 422,
            ];
        }

        if (! Order::query()->where('public_id', $orderPublicId)->exists()) {
            return [
                'payload' => [
                    'message'         => 'Order not found.',
                    'order_public_id' => $orderPublicId,
                ],
                'statusCode' => 404,
            ];
        }

        $orderPayload = $payload['order'] ?? [];
        $payload['order'] = [
            ...$orderPayload,
            'public_id' => $orderPublicId,
        ];

        try {
            $order = DB::transaction(fn (): Order => $this->updateOrder($payload));
        } catch (InvalidArgumentException $invalidArgumentException) {
            return [
                'payload' => [
                    'message' => $invalidArgumentException->getMessage(),
                ],
                'statusCode' => 422,
            ];
        }

        return $this->successResponse($order, 200);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{payload: array<string, mixed>, statusCode: int}
     */
    public function delete(array $payload): array
    {
        $orderPublicId = $payload['order_public_id'] ?? '';
        $order = Order::query()->where('public_id', $orderPublicId)->first();
        if ($order === null) {
            return [
                'payload' => [
                    'message'         => 'Order not found.',
                    'order_public_id' => $orderPublicId,
                ],
                'statusCode' => 404,
            ];
        }

        $order->delete();

        return [
            'payload' => [
                'status'          => 'deleted',
                'order_public_id' => $orderPublicId,
            ],
            'statusCode' => 200,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function createOrder(array $payload): Order
    {
        $orderPayload = $payload['order'] ?? [];
        $restaurant = $this->resolveRestaurant($orderPayload['restaurant_public_id'] ?? '');
        $customer = $this->resolveCustomer($restaurant, $payload['customer'] ?? []);
        $deliveryAddress = $this->resolveDeliveryAddress($customer, $payload['delivery_address'] ?? []);

        $orderPublicId = $orderPayload['public_id'] ?? '';
        throw_if($orderPublicId === '', InvalidArgumentException::class, 'Order public id is required.');

        $requestedAtUtc = CarbonImmutable::parse($orderPayload['request_delivery_time'] ?? 'now')->utc();
        $currency = $restaurant->getRawOriginal('currency');

        $order = Order::query()->create([
            'public_id'             => $orderPublicId,
            'restaurant_id'         => $restaurant->getKey(),
            'customer_id'           => $customer->getKey(),
            'delivery_address_id'   => $deliveryAddress->getKey(),
            'request_delivery_time' => $requestedAtUtc,
            'type'                  => OrderType::from($orderPayload['type'] ?? OrderType::ASAP->value),
            'status'                => OrderStatus::from($orderPayload['status'] ?? OrderStatus::NEW->value),
            'total_cents'           => $orderPayload['total_cents'] ?? 0,
            'delivery_fee_cents'    => $orderPayload['delivery_fee_cents'] ?? 0,
            'currency'              => $orderPayload['currency'] ?? $currency,
        ]);

        $this->syncOrderItems($order, $payload['order_item'] ?? [], $currency);

        return $order->fresh(['items.modifiers']) ?? $order;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function updateOrder(array $payload): Order
    {
        $orderPayload = $payload['order'] ?? [];
        $restaurant = $this->resolveRestaurant($orderPayload['restaurant_public_id'] ?? '');
        $customer = $this->resolveCustomer($restaurant, $payload['customer'] ?? []);
        $deliveryAddress = $this->resolveDeliveryAddress($customer, $payload['delivery_address'] ?? []);

        $orderPublicId = $orderPayload['public_id'] ?? '';
        throw_if($orderPublicId === '', InvalidArgumentException::class, 'Order public id is required.');

        $order = Order::query()->where('public_id', $orderPublicId)->first();
        throw_if($order === null, InvalidArgumentException::class, 'Order not found.');

        $requestedAtUtc = CarbonImmutable::parse($orderPayload['request_delivery_time'] ?? 'now')->utc();
        $currency = $restaurant->getRawOriginal('currency');

        $order->fill([
            'restaurant_id'         => $restaurant->getKey(),
            'customer_id'           => $customer->getKey(),
            'delivery_address_id'   => $deliveryAddress->getKey(),
            'request_delivery_time' => $requestedAtUtc,
            'type'                  => OrderType::from($orderPayload['type'] ?? OrderType::ASAP->value),
            'status'                => OrderStatus::from($orderPayload['status'] ?? OrderStatus::NEW->value),
            'total_cents'           => $orderPayload['total_cents'] ?? 0,
            'delivery_fee_cents'    => $orderPayload['delivery_fee_cents'] ?? 0,
            'currency'              => $orderPayload['currency'] ?? $currency,
        ]);
        $order->save();

        $this->syncOrderItems($order, $payload['order_item'] ?? [], $currency);

        return $order->fresh(['items.modifiers']) ?? $order;
    }

    private function resolveRestaurant(string $restaurantPublicId): Restaurant
    {
        $restaurant = Restaurant::query()->where('public_id', $restaurantPublicId)->first();
        throw_if($restaurant === null, InvalidArgumentException::class, 'Restaurant not found.');

        return $restaurant;
    }

    /**
     * @param  array<string, mixed>  $customerPayload
     */
    private function resolveCustomer(Restaurant $restaurant, array $customerPayload): Customer
    {
        $customerPublicId = $customerPayload['public_id'] ?? '';
        throw_if($customerPublicId === '', InvalidArgumentException::class, 'Customer public id is required.');

        $customer = Customer::query()
            ->where('restaurant_id', $restaurant->getKey())
            ->where('public_id', $customerPublicId)
            ->first();

        if ($customer !== null) {
            return $customer;
        }

        $name = $customerPayload['name'] ?? '';
        $phone = $customerPayload['phone'] ?? '';
        throw_if($name === '' || $phone === '', InvalidArgumentException::class, 'Customer name and phone are required for new customer.');

        return Customer::query()->create([
            'restaurant_id' => $restaurant->getKey(),
            'public_id'     => $customerPublicId,
            'name'          => $name,
            'phone'         => $phone,
            'email'         => $customerPayload['email'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $deliveryAddressPayload
     */
    private function resolveDeliveryAddress(Customer $customer, array $deliveryAddressPayload): DeliveryAddress
    {
        $addressPublicId = $deliveryAddressPayload['public_id'] ?? '';
        if ($addressPublicId !== '') {
            $deliveryAddress = DeliveryAddress::query()->where('public_id', $addressPublicId)->first();
            throw_if($deliveryAddress === null, InvalidArgumentException::class, 'Delivery address not found for provided public id.');

            throw_if((int) $deliveryAddress->customer_id !== (int) $customer->getKey(), InvalidArgumentException::class, 'Delivery address belongs to another customer.');

            return $deliveryAddress;
        }

        return DeliveryAddress::query()->create([
            'public_id'   => (string) Str::uuid(),
            'customer_id' => $customer->getKey(),
            'city'        => $deliveryAddressPayload['city'] ?? '',
            'street'      => $deliveryAddressPayload['street'] ?? '',
            'building'    => $deliveryAddressPayload['building'] ?? '',
            'apartment'   => $deliveryAddressPayload['apartment'] ?? null,
            'comment'     => $deliveryAddressPayload['comment'] ?? null,
        ]);
    }

    /**
     * @param  array<int, mixed>  $orderItemsPayload
     */
    private function syncOrderItems(Order $order, array $orderItemsPayload, string $currency): void
    {
        $itemPublicIds = [];

        foreach ($orderItemsPayload as $itemPayload) {
            $itemPublicId = $itemPayload['public_id'] ?? '';
            if ($itemPublicId === '') {
                continue;
            }

            $itemPublicIds[] = $itemPublicId;
            $orderItem = OrderItem::query()->where('public_id', $itemPublicId)->first();
            if ($orderItem === null) {
                $orderItem = OrderItem::query()->create([
                    'public_id'   => $itemPublicId,
                    'order_id'    => $order->getKey(),
                    'sku'         => $itemPayload['sku'] ?? $itemPublicId,
                    'quantity'    => $itemPayload['quantity'] ?? 1,
                    'price_cents' => $itemPayload['price_cents'] ?? 0,
                    'currency'    => $itemPayload['currency'] ?? $currency,
                ]);
            } else {
                $orderItem->fill([
                    'order_id'    => $order->getKey(),
                    'sku'         => $itemPayload['sku'] ?? $itemPublicId,
                    'quantity'    => $itemPayload['quantity'] ?? 1,
                    'price_cents' => $itemPayload['price_cents'] ?? 0,
                    'currency'    => $itemPayload['currency'] ?? $currency,
                ]);
                $orderItem->save();
            }

            $this->syncModifiers($orderItem, $itemPayload['modifiers'] ?? [], $currency);
        }

        OrderItem::query()
            ->where('order_id', $order->getKey())
            ->whereNotIn('public_id', $itemPublicIds)
            ->delete();
    }

    /**
     * @param  array<int, mixed>  $modifiersPayload
     */
    private function syncModifiers(OrderItem $orderItem, array $modifiersPayload, string $currency): void
    {
        $modifierPublicIds = [];

        foreach ($modifiersPayload as $modifierPayload) {
            $modifierPublicId = $modifierPayload['public_id'] ?? '';
            if ($modifierPublicId === '') {
                continue;
            }

            $modifierPublicIds[] = $modifierPublicId;

            $modifier = OrderItemModifier::query()->where('public_id', $modifierPublicId)->first();
            if ($modifier === null) {
                OrderItemModifier::query()->create([
                    'public_id'     => $modifierPublicId,
                    'order_item_id' => $orderItem->getKey(),
                    'type'          => $modifierPayload['type'] ?? 'add',
                    'quantity'      => $modifierPayload['quantity'] ?? 1,
                    'price_cents'   => $modifierPayload['price_cents'] ?? 0,
                    'currency'      => $modifierPayload['currency'] ?? $currency,
                ]);
            } else {
                $modifier->fill([
                    'order_item_id' => $orderItem->getKey(),
                    'type'          => $modifierPayload['type'] ?? 'add',
                    'quantity'      => $modifierPayload['quantity'] ?? 1,
                    'price_cents'   => $modifierPayload['price_cents'] ?? 0,
                    'currency'      => $modifierPayload['currency'] ?? $currency,
                ]);
                $modifier->save();
            }
        }

        OrderItemModifier::query()
            ->where('order_item_id', $orderItem->getKey())
            ->whereNotIn('public_id', $modifierPublicIds)
            ->delete();
    }

    /**
     * @return array{payload: array<string, mixed>, statusCode: int}
     */
    private function successResponse(Order $order, int $statusCode): array
    {
        return [
            'payload' => [
                'status'          => 'ok',
                'order_public_id' => (string) $order->public_id,
                'order_status'    => (string) $order->getRawOriginal('status'),
            ],
            'statusCode' => $statusCode,
        ];
    }
}
