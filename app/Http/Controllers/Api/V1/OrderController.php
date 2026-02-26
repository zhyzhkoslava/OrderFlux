<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpsertOrderRequest;
use App\Services\Orders\OrderService;
use Illuminate\Http\JsonResponse;

final class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function create(UpsertOrderRequest $request): JsonResponse
    {
        $result = $this->orderService->create($request->validated());

        return response()->json($result['payload'], $result['statusCode']);
    }

    public function update(UpsertOrderRequest $request, string $orderPublicId): JsonResponse
    {
        $result = $this->orderService->update([
            ...$request->validated(),
            'order_public_id' => $orderPublicId,
        ]);

        return response()->json($result['payload'], $result['statusCode']);
    }

    public function delete(string $orderPublicId): JsonResponse
    {
        $result = $this->orderService->delete([
            'order_public_id' => $orderPublicId,
        ]);

        return response()->json($result['payload'], $result['statusCode']);
    }
}
