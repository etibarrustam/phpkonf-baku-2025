<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['service' => 'delivery-service', 'version' => '1.0']);
});
