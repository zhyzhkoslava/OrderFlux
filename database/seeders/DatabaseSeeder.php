<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemModifier;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $restaurant = Restaurant::factory()->create();
        $customers = Customer::factory()->count(2)->create();

        foreach ($customers as $customer) {
            $order = Order::factory()
                ->for($restaurant)
                ->for($customer)
                ->create();

            $items = OrderItem::factory()->count(2)->for($order)->create();

            foreach ($items as $item) {
                OrderItemModifier::factory()->count(2)->for($item)->create();
            }
        }
    }
}
