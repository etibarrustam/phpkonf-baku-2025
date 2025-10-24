<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class KitchenService
{
    public function addToQueue(Order $order): void
    {
        Log::info("Order #{$order->id} added to kitchen queue");

        $order->update(['status' => Order::STATUS_PREPARING]);
    }

    public function markAsReady(Order $order): void
    {
        if ($order->status !== Order::STATUS_PREPARING) {
            throw new \Exception('Order is not in preparing status');
        }

        $order->update(['status' => Order::STATUS_READY]);

        Log::info("Order #{$order->id} is ready for delivery");
    }

    public function getKitchenQueue(): array
    {
        return Order::where('status', Order::STATUS_PREPARING)
            ->with(['customer', 'product'])
            ->orderBy('created_at')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'product' => $order->product->name,
                    'quantity' => $order->quantity,
                    'customer' => $order->customer->name,
                    'time_in_queue' => $order->created_at->diffForHumans(),
                ];
            })
            ->toArray();
    }
}
