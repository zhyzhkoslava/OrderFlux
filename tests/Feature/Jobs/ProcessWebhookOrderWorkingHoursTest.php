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
use RuntimeException;
use Tests\Feature\Support\BuildsWebhookOrderPayload;
use Tests\TestCase;

final class ProcessWebhookOrderWorkingHoursTest extends TestCase
{
    use BuildsWebhookOrderPayload;
    use RefreshDatabase;

    #[Test]
    public function it_rejects_orders_outside_restaurant_working_hours(): void
    {
        $restaurant = Restaurant::factory()->create([
            'public_id' => 'rest-001',
            'timezone'  => 'America/New_York',
            'currency'  => Currency::USD,
            'opens_at'  => '11:00:00',
            'closes_at' => '12:00:00',
        ]);

        $payload = $this->validPayload($restaurant->public_id);

        $job = new ProcessWebhookOrderJob($payload);
        $this->expectExceptionMessage('Restaurant is not accepting orders at requested time.');
        $this->expectException(RuntimeException::class);

        try {
            $job->handle(resolve(WebhookOrderConsumerService::class));
        } finally {
            self::assertDatabaseCount('orders', 0);
            self::assertNull(Order::query()->where('public_id', 'ord-001')->first());
        }
    }
}
