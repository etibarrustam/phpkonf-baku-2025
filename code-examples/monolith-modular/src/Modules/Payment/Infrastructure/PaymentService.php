<?php

namespace Modules\Payment\Infrastructure;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Payment\Application\PaymentServiceInterface;

class PaymentService implements PaymentServiceInterface
{
    public function processPayment(int $orderId, float $amount): bool
    {
        Log::info('Processing payment', ['order_id' => $orderId, 'amount' => $amount]);

        $paymentId = DB::table('payments')->insertGetId([
            'order_id' => $orderId,
            'amount' => $amount,
            'status' => 'processing',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $success = $this->simulatePaymentGateway($amount);

        DB::table('payments')
            ->where('id', $paymentId)
            ->update([
                'status' => $success ? 'completed' : 'failed',
                'updated_at' => now(),
            ]);

        Log::info('Payment processed', ['order_id' => $orderId, 'success' => $success]);

        return $success;
    }

    public function refundPayment(int $orderId, float $amount): bool
    {
        Log::info('Processing refund', ['order_id' => $orderId, 'amount' => $amount]);

        $payment = DB::table('payments')
            ->where('order_id', $orderId)
            ->where('status', 'completed')
            ->first();

        if (!$payment) {
            return false;
        }

        DB::table('payments')
            ->where('id', $payment->id)
            ->update([
                'status' => 'refunded',
                'updated_at' => now(),
            ]);

        Log::info('Refund processed', ['order_id' => $orderId]);

        return true;
    }

    private function simulatePaymentGateway(float $amount): bool
    {
        usleep(100000);
        return true;
    }
}
