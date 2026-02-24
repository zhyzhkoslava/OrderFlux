<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Webhook;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Webhook\StoreOrderWebhookRequest;
use App\Services\Orders\WebhookOrderIngestionService;
use Illuminate\Http\JsonResponse;

final class OrderWebhookController extends Controller
{
    public function __construct(private readonly WebhookOrderIngestionService $webhookOrderIngestionService) {}

    public function __invoke(StoreOrderWebhookRequest $request): JsonResponse
    {
        $result = $this->webhookOrderIngestionService->ingest($request->validated());

        return response()->json($result->payload, $result->statusCode);
    }
}
