<?php

use App\Http\Controllers\DeliveryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['service' => 'delivery-service', 'version' => '1.0']);
});

Route::get('/health', [DeliveryController::class, 'health']);
Route::get('/deliveries', [DeliveryController::class, 'index']);
