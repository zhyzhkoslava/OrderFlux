<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CustomerApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_customer_crud_flow(): void
    {
        $payload = [
            'phone' => '+380971112233',
            'name'  => 'Jane Doe',
            'email' => 'jane@example.com',
        ];

        $createResponse = $this->postJson('/api/customers', $payload);
        $createResponse->assertCreated();
        $createResponse->assertJsonMissing(['id']);

        $customer = Customer::query()->firstOrFail();

        $this->getJson('/api/customers')->assertOk();

        $showResponse = $this->getJson('/api/customers/'.$customer->public_id);
        $showResponse->assertOk();
        $showResponse->assertJsonPath('data.public_id', $customer->public_id);

        $updateResponse = $this->putJson('/api/customers/'.$customer->public_id, [
            'name' => 'Jane Updated',
        ]);
        $updateResponse->assertOk();

        $this->assertDatabaseHas('customers', [
            'id'   => $customer->id,
            'name' => 'Jane Updated',
        ]);

        $this->deleteJson('/api/customers/'.$customer->public_id)->assertNoContent();
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    #[Test]
    public function test_customer_validation_errors_are_returned(): void
    {
        $response = $this->postJson('/api/customers', [
            'email' => 'not-an-email',
            'phone' => str_repeat('1', 33),
            'name'  => str_repeat('a', 256),
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['email', 'phone', 'name']);
    }

    #[Test]
    public function test_customer_show_returns_not_found_for_unknown_public_id(): void
    {
        $this->getJson('/api/customers/00000000-0000-0000-0000-000000000000')
            ->assertNotFound();
    }
}
