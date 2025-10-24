<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\DeliveryController;

Route::prefix('orders')->group(function () {
    Route::post('/', [OrderController::class, 'store']);
    Route::get('/{id}', [OrderController::class, 'show']);
    Route::patch('/{id}/status', [OrderController::class, 'updateStatus']);
    Route::post('/{id}/cancel', [OrderController::class, 'cancel']);
});

Route::prefix('kitchen')->group(function () {
    Route::get('/queue', [KitchenController::class, 'queue']);
    Route::post('/orders/{id}/ready', [KitchenController::class, 'markReady']);
});

Route::prefix('delivery')->group(function () {
    Route::get('/active', [DeliveryController::class, 'activeDeliveries']);
    Route::post('/orders/{id}/delivered', [DeliveryController::class, 'markDelivered']);
});
