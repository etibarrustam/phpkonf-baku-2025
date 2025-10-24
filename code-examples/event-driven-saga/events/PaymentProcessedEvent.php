<?php

namespace App\Events;

class PaymentProcessedEvent
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $transactionId,
        public readonly float $amount,
        public readonly string $status,
        public readonly string $occurredAt
    ) {}

    public function toArray(): array
    {
        return [
            'event_type' => 'payment.processed',
            'event_id' => uniqid('evt_'),
            'occurred_at' => $this->occurredAt,
            'data' => [
                'order_id' => $this->orderId,
                'transaction_id' => $this->transactionId,
                'amount' => $this->amount,
                'status' => $this->status,
            ]
        ];
    }
}
