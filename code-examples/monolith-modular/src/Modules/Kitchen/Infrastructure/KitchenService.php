<?php

namespace Modules\Kitchen\Infrastructure;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Kitchen\Application\KitchenServiceInterface;
use Modules\Order\Domain\OrderRepositoryInterface;
use Modules\Order\Domain\OrderStatus;

class KitchenService implements KitchenServiceInterface
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository
    ) {}

    public function addToQueue(int $orderId): void
    {
        Log::info('Adding order to kitchen queue', ['order_id' => $orderId]);

        DB::table('kitchen_queue')->insert([
            'order_id' => $orderId,
            'status' => 'queued',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $order = $this->orderRepository->findById($orderId);
        if ($order) {
            $order->updateStatus(OrderStatus::PREPARING);
            $this->orderRepository->save($order);
        }
    }

    public function markAsReady(int $orderId): void
    {
        Log::info('Marking order as ready', ['order_id' => $orderId]);

        DB::table('kitchen_queue')
            ->where('order_id', $orderId)
            ->update([
                'status' => 'ready',
                'updated_at' => now(),
            ]);

        $order = $this->orderRepository->findById($orderId);
        if ($order) {
            $order->updateStatus(OrderStatus::READY);
            $this->orderRepository->save($order);
        }
    }

    public function getQueue(): array
    {
        $items = DB::table('kitchen_queue')
            ->where('status', 'queued')
            ->get();

        return $items->map(fn($item) => [
            'order_id' => $item->order_id,
            'status' => $item->status,
            'created_at' => $item->created_at,
        ])->toArray();
    }
}
