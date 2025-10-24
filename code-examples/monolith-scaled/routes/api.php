<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\DeliveryController;
use Illuminate\Support\Facades\Route;

Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{id}', [OrderController::class, 'show']);
Route::get('/kitchen/queue', [KitchenController::class, 'queue']);
Route::get('/delivery/active', [DeliveryController::class, 'active']);
