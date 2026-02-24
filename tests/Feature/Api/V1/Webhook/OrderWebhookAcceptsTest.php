<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Webhook;

use App\Jobs\ProcessWebhookOrderJob;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Support\BuildsWebhookOrderPayload;
use Tests\TestCase;

final class OrderWebhookAcceptsTest extends TestCase
{
    use BuildsWebhookOrderPayload;
    use RefreshDatabase;

    #[Test]
    public function it_accepts_valid_webhook_and_dispatches_async_job(): void
    {
        Queue::fake();

        $restaurant = Restaurant::factory()->create();
        $payload = $this->validPayload($restaurant->public_id);

        $response = $this->postJson('/api/v1/webhook/orders', $payload);

        $response
            ->assertAccepted()
            ->assertJson([
                'status'          => 'accepted',
                'order_public_id' => data_get($payload, 'order.public_id'),
            ]);

        Queue::assertPushed(ProcessWebhookOrderJob::class, 1);
    }
}
