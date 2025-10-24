<?php

use App\Http\Controllers\DeliveryController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [DeliveryController::class, 'health']);
Route::post('/deliveries', [DeliveryController::class, 'create']);
Route::get('/deliveries/{id}', [DeliveryController::class, 'show']);
Route::get('/deliveries', [DeliveryController::class, 'index']);
Route::patch('/deliveries/{id}/status', [DeliveryController::class, 'updateStatus']);
