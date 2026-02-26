<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('orders', [OrderController::class, 'create'])->name('orders.create');
    Route::put('orders/{orderPublicId}', [OrderController::class, 'update'])->name('orders.update');
    Route::delete('orders/{orderPublicId}', [OrderController::class, 'delete'])->name('orders.delete');
});
