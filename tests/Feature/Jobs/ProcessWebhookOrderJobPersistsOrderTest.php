<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Enums\Currency;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Jobs\ProcessWebhookOrderJob;
use App\Models\Order;
use App\Models\Restaurant;
use App\Services\Orders\WebhookOrderConsumerService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Support\BuildsWebhookOrderPayload;
use Tests\TestCase;

final class ProcessWebhookOrderJobPersistsOrderTest extends TestCase
{
    use BuildsWebhookOrderPayload;
    use RefreshDatabase;

    #[Test]
    public function it_persists_order_header_fields(): void
    {
        $restaurant = Restaurant::factory()->create([
            'public_id' => 'rest-001',
            'timezone'  => 'America/New_York',
            'currency'  => Currency::USD,
        ]);

        $payload = $this->validPayload($restaurant->public_id);

        $job = new ProcessWebhookOrderJob($payload);
        $job->handle(resolve(WebhookOrderConsumerService::class));

        $order = Order::query()
            ->where('public_id', 'ord-001')
            ->firstOrFail();

        self::assertSame($restaurant->getKey(), $order->restaurant_id);
        self::assertSame(OrderType::ASAP, $order->type);
        self::assertSame(OrderStatus::NEW, $order->status);
        self::assertSame(
            CarbonImmutable::parse('2026-02-23T15:00:00Z', 'UTC')->toIso8601String(),
            $order->scheduled_at->utc()->toIso8601String(),
        );
    }
}
