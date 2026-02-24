<?php

declare(strict_types=1);

namespace App\Services\Orders;

use App\Jobs\ProcessWebhookOrderJob;
use App\Models\Order;
use Illuminate\Support\Facades\Cache;

final class WebhookOrderIngestionService
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function ingest(array $payload): WebhookOrderIngestionResult
    {
        $orderPublicId = (string) data_get($payload, 'order.public_id');

        $existingOrder = Order::query()->where('public_id', $orderPublicId)->first();
        if ($existingOrder !== null) {
            return new WebhookOrderIngestionResult(200, [
                'status'          => 'already_processed',
                'order_public_id' => $existingOrder->public_id,
                'order_status'    => (string) $existingOrder->getRawOriginal('status'),
            ]);
        }

        $responseCacheKey = sprintf('webhook:orders:ingestion:%s:response', $orderPublicId);
        $cachedResponse = Cache::get($responseCacheKey);
        if (is_array($cachedResponse)) {
            return new WebhookOrderIngestionResult(202, $cachedResponse);
        }

        $lock = Cache::lock(sprintf('webhook:orders:ingestion:%s:lock', $orderPublicId), 5);

        return $lock->block(3, function () use ($orderPublicId, $payload, $responseCacheKey): WebhookOrderIngestionResult {
            $existingOrder = Order::query()->where('public_id', $orderPublicId)->first();
            if ($existingOrder !== null) {
                return new WebhookOrderIngestionResult(200, [
                    'status'          => 'already_processed',
                    'order_public_id' => $existingOrder->public_id,
                    'order_status'    => (string) $existingOrder->getRawOriginal('status'),
                ]);
            }

            $cachedResponse = Cache::get($responseCacheKey);
            if (is_array($cachedResponse)) {
                return new WebhookOrderIngestionResult(202, $cachedResponse);
            }

            dispatch(new ProcessWebhookOrderJob($payload))
                ->onConnection('rabbitmq')
                ->onQueue((string) config('queue.connections.rabbitmq.queue', 'orders.ingestion'));

            $responsePayload = [
                'status'          => 'accepted',
                'order_public_id' => $orderPublicId,
            ];
            Cache::put($responseCacheKey, $responsePayload, now()->addDay());

            return new WebhookOrderIngestionResult(202, $responsePayload);
        });
    }
}
