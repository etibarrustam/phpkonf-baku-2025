<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    protected $fillable = [
        'order_id',
        'customer_name',
        'driver_name',
        'status',
        'estimated_time'
    ];
}
