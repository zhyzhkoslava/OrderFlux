<?php

declare(strict_types=1);

namespace App\Services\Orders;

use App\Events\OrderProcessed;
use App\Models\Restaurant;
use App\Services\Orders\Persistence\UpsertWebhookCustomerService;
use App\Services\Orders\Persistence\UpsertWebhookDeliveryAddressService;
use App\Services\Orders\Persistence\UpsertWebhookOrderItemsService;
use App\Services\Orders\Persistence\UpsertWebhookOrderService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final readonly class WebhookOrderConsumerService
{
    public function __construct(
        private UpsertWebhookCustomerService $upsertWebhookCustomerService,
        private UpsertWebhookDeliveryAddressService $upsertWebhookDeliveryAddressService,
        private UpsertWebhookOrderService $upsertWebhookOrderService,
        private UpsertWebhookOrderItemsService $upsertWebhookOrderItemsService,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function consume(array $payload): void
    {
        $orderPayload = (array) $payload['order'];
        $restaurantPublicId = (string) $orderPayload['restaurant_public_id'];

        $restaurant = Restaurant::query()
            ->where('public_id', $restaurantPublicId)
            ->firstOrFail();

        $requestedAtUtc = CarbonImmutable::parse((string) $orderPayload['requested_at'], 'UTC')->utc();
        $requestedAtLocal = $requestedAtUtc->setTimezone($restaurant->timezone);

        throw_unless(
            $this->canAcceptOrder($restaurant, $requestedAtLocal),
            RuntimeException::class,
            'Restaurant is not accepting orders at requested time.'
        );

        DB::transaction(function () use ($payload, $restaurant, $requestedAtUtc): void {
            $customer = $this->upsertWebhookCustomerService->upsert($restaurant, (array) $payload['customer']);
            $deliveryAddress = $this->upsertWebhookDeliveryAddressService->upsert($customer, (array) $payload['delivery_address']);
            $order = $this->upsertWebhookOrderService->upsert(
                $restaurant,
                $customer,
                $deliveryAddress,
                $requestedAtUtc,
                (array) $payload['order'],
            );

            $this->upsertWebhookOrderItemsService->upsert($order, (array) $payload['items']);

            DB::afterCommit(static function () use ($order, $restaurant): void {
                event(new OrderProcessed(
                    orderPublicId: $order->public_id,
                    restaurantPublicId: $restaurant->public_id,
                    status: (string) $order->getRawOriginal('status'),
                    scheduledAtUtcIso8601: CarbonImmutable::parse((string) $order->getRawOriginal('scheduled_at'), 'UTC')
                        ->utc()
                        ->toIso8601String(),
                ));
            });
        });
    }

    private function canAcceptOrder(Restaurant $restaurant, CarbonImmutable $requestedAtLocal): bool
    {
        if (! $restaurant->is_active) {
            return false;
        }

        $opensAt = CarbonImmutable::createFromFormat('H:i:s', (string) $restaurant->opens_at, $restaurant->timezone);
        $closesAt = CarbonImmutable::createFromFormat('H:i:s', (string) $restaurant->closes_at, $restaurant->timezone);
        $requestedTime = CarbonImmutable::createFromFormat('H:i:s', $requestedAtLocal->format('H:i:s'), $restaurant->timezone);

        if ($opensAt->lessThanOrEqualTo($closesAt)) {
            return $requestedTime->betweenIncluded($opensAt, $closesAt);
        }

        if ($requestedTime->greaterThanOrEqualTo($opensAt)) {
            return true;
        }

        return $requestedTime->lessThanOrEqualTo($closesAt);
    }
}
