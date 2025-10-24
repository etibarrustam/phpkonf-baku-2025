<?php

namespace App\Services;

use App\Models\Order;

class OrderService
{
    private const UNIT_PRICE = 12.00;

    public function createOrder(array $data): Order
    {
        $totalPrice = self::UNIT_PRICE * $data['quantity'];

        return Order::create([
            'customer_id' => $data['customer_id'],
            'product_id' => $data['product_id'],
            'quantity' => $data['quantity'],
            'total_price' => $totalPrice,
            'status' => 'pending',
            'payment_status' => 'pending',
            'delivery_address' => $data['delivery_address'],
        ]);
    }
}
