<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function processPayment(Order $order): bool
    {
        Log::info("Processing payment for order #{$order->id}");

        $paymentSuccessful = $this->chargeCustomer($order->total_price);

        if ($paymentSuccessful) {
            $order->update(['payment_status' => Order::PAYMENT_STATUS_PAID]);
            Log::info("Payment successful for order #{$order->id}");
            return true;
        }

        $order->update(['payment_status' => Order::PAYMENT_STATUS_FAILED]);
        Log::error("Payment failed for order #{$order->id}");
        return false;
    }

    public function refundPayment(Order $order): bool
    {
        if ($order->payment_status !== Order::PAYMENT_STATUS_PAID) {
            throw new \Exception('Cannot refund unpaid order');
        }

        Log::info("Processing refund for order #{$order->id}");

        $refundSuccessful = $this->refundCustomer($order->total_price);

        if ($refundSuccessful) {
            Log::info("Refund successful for order #{$order->id}");
            return true;
        }

        Log::error("Refund failed for order #{$order->id}");
        return false;
    }

    private function chargeCustomer(float $amount): bool
    {
        sleep(1);

        return rand(1, 100) > 5;
    }

    private function refundCustomer(float $amount): bool
    {
        sleep(1);

        return rand(1, 100) > 2;
    }
}
