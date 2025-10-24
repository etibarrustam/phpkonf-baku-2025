<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'product_id',
        'quantity',
        'total_price',
        'status',
        'payment_status',
        'delivery_address',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'quantity' => 'integer',
    ];
}
