<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitchenOrder extends Model
{
    protected $fillable = [
        'order_id',
        'items',
        'status',
        'preparation_time'
    ];

    protected $casts = [
        'items' => 'array'
    ];
}
