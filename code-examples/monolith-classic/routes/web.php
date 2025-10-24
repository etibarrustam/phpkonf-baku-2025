<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'app' => 'PlovExpress Monolit',
        'status' => 'running',
        'version' => '1.0.0'
    ]);
});
