<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Webhook;

use App\Enums\Currency;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Jobs\ProcessWebhookOrderJob;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Support\BuildsWebhookOrderPayload;
use Tests\TestCase;

final class OrderWebhookIdempotencyTest extends TestCase
{
    use BuildsWebhookOrderPayload;
    use RefreshDatabase;

    #[Test]
    public function it_returns_previous_result_when_order_already_exists(): void
    {
        Queue::fake();

        $restaurant = Restaurant::factory()->create([
            'currency' => Currency::USD,
        ]);
        $customer = Customer::factory()->create([
            'restaurant_id' => $restaurant->getKey(),
        ]);
        $order = Order::factory()->create([
            'public_id'     => 'ord-existing-001',
            'restaurant_id' => $restaurant->getKey(),
            'customer_id'   => $customer->getKey(),
            'currency'      => Currency::USD,
            'type'          => OrderType::ASAP,
            'status'        => OrderStatus::ACCEPTED,
        ]);

        $payload = $this->validPayload($restaurant->public_id);
        data_set($payload, 'order.public_id', $order->public_id);

        $response = $this->postJson('/api/v1/webhook/orders', $payload);

        $response
            ->assertOk()
            ->assertJson([
                'status'          => 'already_processed',
                'order_public_id' => $order->public_id,
                'order_status'    => OrderStatus::ACCEPTED->value,
            ]);

        Queue::assertNothingPushed();
    }

    #[Test]
    public function it_is_idempotent_for_in_flight_payloads_without_duplicate_dispatch(): void
    {
        Queue::fake();

        $restaurant = Restaurant::factory()->create();
        $payload = $this->validPayload($restaurant->public_id);

        $firstResponse = $this->postJson('/api/v1/webhook/orders', $payload);
        $secondResponse = $this->postJson('/api/v1/webhook/orders', $payload);

        $firstResponse->assertAccepted();
        $secondResponse
            ->assertAccepted()
            ->assertJson([
                'status'          => 'accepted',
                'order_public_id' => data_get($payload, 'order.public_id'),
            ]);

        Queue::assertPushed(ProcessWebhookOrderJob::class, 1);
    }
}
