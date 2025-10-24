<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class DeliveryService
{
    public function assignDelivery(Order $order): void
    {
        if ($order->status !== Order::STATUS_READY) {
            throw new \Exception('Order is not ready for delivery');
        }

        Log::info("Assigning delivery for order #{$order->id}");

        $order->update(['status' => Order::STATUS_DELIVERING]);
    }

    public function markAsDelivered(Order $order): void
    {
        if ($order->status !== Order::STATUS_DELIVERING) {
            throw new \Exception('Order is not in delivery status');
        }

        $order->update(['status' => Order::STATUS_DELIVERED]);

        Log::info("Order #{$order->id} delivered successfully");
    }

    public function getActiveDeliveries(): array
    {
        return Order::where('status', Order::STATUS_DELIVERING)
            ->with(['customer', 'product'])
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'customer' => $order->customer->name,
                    'address' => $order->delivery_address,
                    'product' => $order->product->name,
                    'quantity' => $order->quantity,
                ];
            })
            ->toArray();
    }
}
