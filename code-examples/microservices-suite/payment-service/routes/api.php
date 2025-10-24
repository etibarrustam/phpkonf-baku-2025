<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [PaymentController::class, 'health']);
Route::post('/payments', [PaymentController::class, 'create']);
Route::get('/payments/{id}', [PaymentController::class, 'show']);
Route::get('/payments', [PaymentController::class, 'index']);
