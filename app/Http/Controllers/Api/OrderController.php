<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Customer;
use App\Models\DeliveryAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemModifier;
use App\Models\Restaurant;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $orders = Order::query()
            ->with(['restaurant', 'customer', 'deliveryAddress', 'items.modifiers'])
            ->latest()
            ->paginate();

        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request): OrderResource
    {
        $data = $request->validated();

        $order = DB::transaction(function () use ($data): Order {
            $restaurant = Restaurant::query()
                ->where('public_id', $data['restaurant_public_id'])
                ->firstOrFail();

            $customer = Customer::query()
                ->where('public_id', $data['customer_public_id'])
                ->firstOrFail();

            $deliveryAddress = DeliveryAddress::query()->create([
                'line1'        => $data['delivery_address']['line1'],
                'line2'        => $data['delivery_address']['line2'] ?? null,
                'city'         => $data['delivery_address']['city'],
                'region'       => $data['delivery_address']['region'] ?? null,
                'postal_code'  => $data['delivery_address']['postal_code'] ?? null,
                'country_code' => mb_strtoupper((string) $data['delivery_address']['country_code']),
                'instructions' => $data['delivery_address']['instructions'] ?? null,
            ]);

            $orderAmount = 0;

            $order = Order::query()->create([
                'public_id'           => $data['public_id'] ?? null,
                'restaurant_id'       => $restaurant->id,
                'customer_id'         => $customer->id,
                'delivery_address_id' => $deliveryAddress->id,
                'scheduled_at'        => isset($data['scheduled_at'])
                    ? Date::parse($data['scheduled_at'])->utc()
                    : null,
                'type'            => $data['type'],
                'status'          => $data['status'] ?? Order::STATUS_PENDING,
                'order_amount'    => 0,
                'delivery_amount' => $data['delivery_amount'],
            ]);

            foreach ($data['items'] as $itemData) {
                $itemTotal = $itemData['unit_price'] * $itemData['quantity'];

                $orderItem = OrderItem::query()->create([
                    'order_id'    => $order->id,
                    'name'        => $itemData['name'],
                    'sku'         => $itemData['sku'] ?? null,
                    'quantity'    => $itemData['quantity'],
                    'unit_price'  => $itemData['unit_price'],
                    'total_price' => $itemTotal,
                ]);

                $orderAmount += $itemTotal;

                foreach ($itemData['modifiers'] ?? [] as $modifierData) {
                    $modifierQuantity = $modifierData['quantity'] ?? 1;
                    $modifierTotal = $modifierData['unit_price'] * $modifierQuantity;

                    OrderItemModifier::query()->create([
                        'order_item_id' => $orderItem->id,
                        'name'          => $modifierData['name'],
                        'quantity'      => $modifierQuantity,
                        'unit_price'    => $modifierData['unit_price'],
                        'total_price'   => $modifierTotal,
                    ]);

                    $orderAmount += $modifierTotal;
                }
            }

            $order->order_amount = $orderAmount;
            $order->save();

            return $order;
        });

        $order->load(['restaurant', 'customer', 'deliveryAddress', 'items.modifiers']);

        return new OrderResource($order);
    }

    public function show(Order $order): OrderResource
    {
        $order->load(['restaurant', 'customer', 'deliveryAddress', 'items.modifiers']);

        return new OrderResource($order);
    }

    public function update(UpdateOrderRequest $request, Order $order): OrderResource
    {
        $data = $request->validated();

        if (array_key_exists('scheduled_at', $data)) {
            $order->scheduled_at = $data['scheduled_at'] !== null
                ? Date::parse($data['scheduled_at'])->utc()->toDateTimeString()
                : null;
        }

        if (array_key_exists('status', $data)) {
            $order->status = $data['status'];
        }

        $order->save();

        $order->load(['restaurant', 'customer', 'deliveryAddress', 'items.modifiers']);

        return new OrderResource($order);
    }

    public function destroy(Order $order): Response
    {
        $order->delete();

        return response()->noContent();
    }
}
