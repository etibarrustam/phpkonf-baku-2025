<?php

namespace App\Events;

class OrderCreatedEvent
{
    public function __construct(
        public readonly string $orderId,
        public readonly int $customerId,
        public readonly int $productId,
        public readonly int $quantity,
        public readonly float $totalPrice,
        public readonly string $deliveryAddress,
        public readonly string $occurredAt
    ) {}

    public function toArray(): array
    {
        return [
            'event_type' => 'order.created',
            'event_id' => uniqid('evt_'),
            'occurred_at' => $this->occurredAt,
            'data' => [
                'order_id' => $this->orderId,
                'customer_id' => $this->customerId,
                'product_id' => $this->productId,
                'quantity' => $this->quantity,
                'total_price' => $this->totalPrice,
                'delivery_address' => $this->deliveryAddress,
            ]
        ];
    }
}
