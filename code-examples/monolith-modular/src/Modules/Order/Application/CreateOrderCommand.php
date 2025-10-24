<?php

namespace Modules\Order\Application;

class CreateOrderCommand
{
    public function __construct(
        private int $customerId,
        private int $productId,
        private int $quantity,
        private float $totalPrice,
        private string $deliveryAddress
    ) {}

    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getTotalPrice(): float
    {
        return $this->totalPrice;
    }

    public function getDeliveryAddress(): string
    {
        return $this->deliveryAddress;
    }
}
