<?php

namespace Modules\Payment\Application;

interface PaymentServiceInterface
{
    public function processPayment(int $orderId, float $amount): bool;

    public function refundPayment(int $orderId, float $amount): bool;
}
