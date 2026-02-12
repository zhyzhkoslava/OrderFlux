<?php

declare(strict_types=1);

use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\RestaurantController;
use Illuminate\Support\Facades\Route;

Route::get('/restaurants', [RestaurantController::class, 'index']);
Route::post('/restaurants', [RestaurantController::class, 'store']);
Route::get('/restaurants/{restaurant:public_id}', [RestaurantController::class, 'show']);
Route::put('/restaurants/{restaurant:public_id}', [RestaurantController::class, 'update']);
Route::delete('/restaurants/{restaurant:public_id}', [RestaurantController::class, 'destroy']);

Route::get('/customers', [CustomerController::class, 'index']);
Route::post('/customers', [CustomerController::class, 'store']);
Route::get('/customers/{customer:public_id}', [CustomerController::class, 'show']);
Route::put('/customers/{customer:public_id}', [CustomerController::class, 'update']);
Route::delete('/customers/{customer:public_id}', [CustomerController::class, 'destroy']);

Route::get('/orders', [OrderController::class, 'index']);
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{order:public_id}', [OrderController::class, 'show']);
Route::put('/orders/{order:public_id}', [OrderController::class, 'update']);
Route::delete('/orders/{order:public_id}', [OrderController::class, 'destroy']);
