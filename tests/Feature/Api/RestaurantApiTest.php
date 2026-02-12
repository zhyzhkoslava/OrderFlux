<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RestaurantApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_restaurant_crud_flow(): void
    {
        $payload = [
            'name'      => 'OrderFlux Kitchen',
            'slug'      => 'orderflux-kitchen',
            'timezone'  => 'Europe/Kyiv',
            'currency'  => 'UAH',
            'is_active' => true,
            'address'   => [
                'line1'        => 'Main Street 1',
                'line2'        => 'Suite 2',
                'city'         => 'Kyiv',
                'region'       => 'Kyiv',
                'postal_code'  => '01001',
                'country_code' => 'UA',
            ],
        ];

        $createResponse = $this->postJson('/api/restaurants', $payload);

        $createResponse->assertCreated();
        $createResponse->assertJsonMissing(['id']);

        $restaurant = Restaurant::query()->firstOrFail();

        $this->getJson('/api/restaurants')->assertOk();

        $showResponse = $this->getJson('/api/restaurants/'.$restaurant->public_id);
        $showResponse->assertOk();
        $showResponse->assertJsonPath('data.public_id', $restaurant->public_id);

        $updateResponse = $this->putJson('/api/restaurants/'.$restaurant->public_id, [
            'name'     => 'OrderFlux Kitchen Updated',
            'currency' => 'usd',
            'address'  => [
                'line1'        => 'Second Street 10',
                'city'         => 'Kyiv',
                'country_code' => 'ua',
            ],
        ]);

        $updateResponse->assertOk();
        $this->assertDatabaseHas('restaurants', [
            'id'            => $restaurant->id,
            'name'          => 'OrderFlux Kitchen Updated',
            'currency'      => 'USD',
            'address_line1' => 'Second Street 10',
        ]);

        $this->deleteJson('/api/restaurants/'.$restaurant->public_id)->assertNoContent();
        $this->assertDatabaseMissing('restaurants', ['id' => $restaurant->id]);
    }
}
