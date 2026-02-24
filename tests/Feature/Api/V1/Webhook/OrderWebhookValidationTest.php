<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Webhook;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Support\BuildsWebhookOrderPayload;
use Tests\TestCase;

final class OrderWebhookValidationTest extends TestCase
{
    use BuildsWebhookOrderPayload;
    use RefreshDatabase;

    #[Test]
    public function it_validates_restaurant_public_id(): void
    {
        Queue::fake();

        $payload = $this->validPayload('restaurant-missing');
        $response = $this->postJson('/api/v1/webhook/orders', $payload);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'order.restaurant_public_id',
            ]);
    }
}
