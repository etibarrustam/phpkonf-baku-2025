<?php

namespace Modules\Delivery\Infrastructure;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Delivery\Application\DeliveryServiceInterface;
use Modules\Order\Domain\OrderRepositoryInterface;
use Modules\Order\Domain\OrderStatus;

class DeliveryService implements DeliveryServiceInterface
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository
    ) {}

    public function assignDelivery(int $orderId): void
    {
        Log::info('Assigning delivery', ['order_id' => $orderId]);

        DB::table('deliveries')->insert([
            'order_id' => $orderId,
            'status' => 'assigned',
            'driver_id' => rand(1, 10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $order = $this->orderRepository->findById($orderId);
        if ($order) {
            $order->updateStatus(OrderStatus::DELIVERING);
            $this->orderRepository->save($order);
        }
    }

    public function markAsDelivered(int $orderId): void
    {
        Log::info('Marking order as delivered', ['order_id' => $orderId]);

        DB::table('deliveries')
            ->where('order_id', $orderId)
            ->update([
                'status' => 'delivered',
                'delivered_at' => now(),
                'updated_at' => now(),
            ]);

        $order = $this->orderRepository->findById($orderId);
        if ($order) {
            $order->updateStatus(OrderStatus::DELIVERED);
            $this->orderRepository->save($order);
        }
    }

    public function getActiveDeliveries(): array
    {
        $deliveries = DB::table('deliveries')
            ->where('status', 'assigned')
            ->get();

        return $deliveries->map(fn($delivery) => [
            'order_id' => $delivery->order_id,
            'driver_id' => $delivery->driver_id,
            'status' => $delivery->status,
            'created_at' => $delivery->created_at,
        ])->toArray();
    }
}
