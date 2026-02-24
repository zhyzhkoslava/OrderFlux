<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Enums\Currency;
use App\Events\OrderProcessed;
use App\Jobs\ProcessWebhookOrderJob;
use App\Models\Restaurant;
use App\Services\Orders\WebhookOrderConsumerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Support\BuildsWebhookOrderPayload;
use Tests\TestCase;

final class ProcessWebhookOrderBroadcastsEventTest extends TestCase
{
    use BuildsWebhookOrderPayload;
    use RefreshDatabase;

    #[Test]
    public function it_broadcasts_order_processed_event_after_successful_commit(): void
    {
        Event::fake([OrderProcessed::class]);

        $restaurant = Restaurant::factory()->create([
            'public_id' => 'rest-001',
            'timezone'  => 'America/New_York',
            'currency'  => Currency::USD,
            'opens_at'  => '09:00:00',
            'closes_at' => '22:00:00',
        ]);

        $payload = $this->validPayload($restaurant->public_id);

        $job = new ProcessWebhookOrderJob($payload);
        $job->handle(resolve(WebhookOrderConsumerService::class));

        Event::assertDispatched(static fn (OrderProcessed $event): bool => $event->restaurantPublicId === $restaurant->public_id
            && $event->orderPublicId === 'ord-001'
            && $event->status === 'NEW');
    }
}
