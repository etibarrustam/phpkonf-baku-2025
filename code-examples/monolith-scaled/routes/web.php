<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'app' => 'PlovExpress Scalable Monolith',
        'status' => 'running',
        'instance' => getenv('INSTANCE_ID') ?: 'unknown',
        'session_driver' => config('session.driver'),
        'cache_driver' => config('cache.default'),
    ]);
});
