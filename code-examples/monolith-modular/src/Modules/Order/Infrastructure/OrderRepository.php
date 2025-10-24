<?php

namespace Modules\Order\Infrastructure;

use Illuminate\Support\Facades\DB;
use Modules\Order\Domain\OrderEntity;
use Modules\Order\Domain\OrderRepositoryInterface;

class OrderRepository implements OrderRepositoryInterface
{
    public function save(OrderEntity $order): OrderEntity
    {
        if ($order->getId() === null) {
            $id = DB::table('orders')->insertGetId([
                'customer_id' => $order->getCustomerId(),
                'product_id' => $order->getProductId(),
                'quantity' => $order->getQuantity(),
                'total_price' => $order->getTotalPrice(),
                'status' => $order->getStatus(),
                'payment_status' => $order->getPaymentStatus(),
                'delivery_address' => $order->getDeliveryAddress(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return new OrderEntity(
                customerId: $order->getCustomerId(),
                productId: $order->getProductId(),
                quantity: $order->getQuantity(),
                totalPrice: $order->getTotalPrice(),
                deliveryAddress: $order->getDeliveryAddress(),
                id: $id
            );
        }

        DB::table('orders')
            ->where('id', $order->getId())
            ->update([
                'status' => $order->getStatus(),
                'payment_status' => $order->getPaymentStatus(),
                'updated_at' => now(),
            ]);

        return $order;
    }

    public function findById(int $id): ?OrderEntity
    {
        $row = DB::table('orders')->where('id', $id)->first();

        if (!$row) {
            return null;
        }

        return $this->rowToEntity($row);
    }

    public function findByStatus(string $status): array
    {
        $rows = DB::table('orders')->where('status', $status)->get();

        return $rows->map(fn($row) => $this->rowToEntity($row))->toArray();
    }

    public function delete(int $id): bool
    {
        return DB::table('orders')->where('id', $id)->delete() > 0;
    }

    private function rowToEntity($row): OrderEntity
    {
        $entity = new OrderEntity(
            customerId: $row->customer_id,
            productId: $row->product_id,
            quantity: $row->quantity,
            totalPrice: $row->total_price,
            deliveryAddress: $row->delivery_address,
            id: $row->id
        );

        $entity->updateStatus($row->status);

        if ($row->payment_status === 'paid') {
            $entity->markAsPaid();
        } elseif ($row->payment_status === 'failed') {
            $entity->markAsPaymentFailed();
        }

        return $entity;
    }
}
