<?php

use App\Http\Controllers\KitchenController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [KitchenController::class, 'health']);
Route::post('/kitchen/prepare', [KitchenController::class, 'prepare']);
Route::get('/kitchen/{id}', [KitchenController::class, 'show']);
Route::get('/kitchen', [KitchenController::class, 'index']);
Route::patch('/kitchen/{id}/status', [KitchenController::class, 'updateStatus']);
