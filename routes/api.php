<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Webhook\OrderWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('webhook/orders', OrderWebhookController::class);
});
