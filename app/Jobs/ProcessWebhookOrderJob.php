<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\Orders\WebhookOrderConsumerService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class ProcessWebhookOrderJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(public readonly array $payload) {}

    public function uniqueId(): string
    {
        return (string) data_get($this->payload, 'order.public_id');
    }

    public function handle(WebhookOrderConsumerService $webhookOrderConsumerService): void
    {
        $webhookOrderConsumerService->consume($this->payload);
    }
}
