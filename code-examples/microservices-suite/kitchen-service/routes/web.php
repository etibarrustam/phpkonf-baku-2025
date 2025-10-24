<?php

use App\Http\Controllers\KitchenController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['service' => 'kitchen-service', 'version' => '1.0']);
});

Route::get('/health', [KitchenController::class, 'health']);
Route::get('/kitchen', [KitchenController::class, 'index']);
