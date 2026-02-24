<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Enums\Currency;
use App\Jobs\ProcessWebhookOrderJob;
use App\Models\Order;
use App\Models\Restaurant;
use App\Services\Orders\WebhookOrderConsumerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Support\BuildsWebhookOrderPayload;
use Tests\TestCase;

final class ProcessWebhookOrderJobPersistsItemsTest extends TestCase
{
    use BuildsWebhookOrderPayload;
    use RefreshDatabase;

    #[Test]
    public function it_persists_order_items_and_modifiers(): void
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
            ->with('items.modifiers')
            ->firstOrFail();

        self::assertCount(1, $order->items);
        self::assertCount(1, $order->items->firstOrFail()->modifiers);
    }
}
