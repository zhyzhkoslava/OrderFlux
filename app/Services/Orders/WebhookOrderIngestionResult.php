<?php

declare(strict_types=1);

namespace App\Services\Orders;

final readonly class WebhookOrderIngestionResult
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public int $statusCode,
        public array $payload,
    ) {}
}
