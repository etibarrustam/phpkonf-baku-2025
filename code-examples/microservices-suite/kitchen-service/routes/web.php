<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['service' => 'kitchen-service', 'version' => '1.0']);
});
