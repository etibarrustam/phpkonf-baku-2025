<?php

namespace Modules\Order\Application;

use Modules\Order\Domain\OrderEntity;
use Modules\Order\Domain\OrderRepositoryInterface;
use Modules\Payment\Application\PaymentServiceInterface;
use Modules\Kitchen\Application\KitchenServiceInterface;

class CreateOrderUseCase
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private PaymentServiceInterface $paymentService,
        private KitchenServiceInterface $kitchenService
    ) {}

    public function execute(CreateOrderCommand $command): OrderEntity
    {
        $order = new OrderEntity(
            customerId: $command->getCustomerId(),
            productId: $command->getProductId(),
            quantity: $command->getQuantity(),
            totalPrice: $command->getTotalPrice(),
            deliveryAddress: $command->getDeliveryAddress()
        );

        $order = $this->orderRepository->save($order);

        $paymentResult = $this->paymentService->processPayment($order->getId(), $order->getTotalPrice());

        if ($paymentResult) {
            $order->markAsPaid();
            $this->kitchenService->addToQueue($order->getId());
        } else {
            $order->markAsPaymentFailed();
        }

        return $this->orderRepository->save($order);
    }
}
