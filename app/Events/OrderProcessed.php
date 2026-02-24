<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class OrderProcessed implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly string $orderPublicId,
        public readonly string $restaurantPublicId,
        public readonly string $status,
        public readonly string $scheduledAtUtcIso8601,
    ) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel(sprintf('restaurants.%s.orders', $this->restaurantPublicId)),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.processed';
    }

    /**
     * @return array<string, string>
     */
    public function broadcastWith(): array
    {
        return [
            'order_public_id'      => $this->orderPublicId,
            'restaurant_public_id' => $this->restaurantPublicId,
            'status'               => $this->status,
            'scheduled_at_utc'     => $this->scheduledAtUtcIso8601,
        ];
    }
}
