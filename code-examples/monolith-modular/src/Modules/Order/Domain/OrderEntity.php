<?php

namespace Modules\Order\Domain;

class OrderEntity
{
    private ?int $id;
    private int $customerId;
    private int $productId;
    private int $quantity;
    private float $totalPrice;
    private string $status;
    private string $paymentStatus;
    private string $deliveryAddress;

    public function __construct(
        int $customerId,
        int $productId,
        int $quantity,
        float $totalPrice,
        string $deliveryAddress,
        ?int $id = null
    ) {
        $this->id = $id;
        $this->customerId = $customerId;
        $this->productId = $productId;
        $this->quantity = $quantity;
        $this->totalPrice = $totalPrice;
        $this->deliveryAddress = $deliveryAddress;
        $this->status = OrderStatus::PENDING;
        $this->paymentStatus = PaymentStatus::PENDING;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getPaymentStatus(): string
    {
        return $this->paymentStatus;
    }

    public function getDeliveryAddress(): string
    {
        return $this->deliveryAddress;
    }

    public function markAsPaid(): void
    {
        $this->paymentStatus = PaymentStatus::PAID;
    }

    public function markAsPaymentFailed(): void
    {
        $this->paymentStatus = PaymentStatus::FAILED;
    }

    public function updateStatus(string $status): void
    {
        if (!OrderStatus::isValid($status)) {
            throw new \InvalidArgumentException("Invalid order status: {$status}");
        }

        $this->status = $status;
    }

    public function cancel(): void
    {
        if (in_array($this->status, [OrderStatus::DELIVERED, OrderStatus::CANCELLED])) {
            throw new \DomainException('Cannot cancel order in current status');
        }

        $this->status = OrderStatus::CANCELLED;
    }
}
