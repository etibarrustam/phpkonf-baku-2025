<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private KitchenService $kitchenService,
        private PaymentService $paymentService,
        private DeliveryService $deliveryService
    ) {}

    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $customer = Customer::findOrFail($data['customer_id']);
            $product = Product::findOrFail($data['product_id']);

            if (!$product->is_available) {
                throw new \Exception('Product is not available');
            }

            $totalPrice = $product->price * $data['quantity'];

            $order = Order::create([
                'customer_id' => $customer->id,
                'product_id' => $product->id,
                'quantity' => $data['quantity'],
                'total_price' => $totalPrice,
                'delivery_address' => $data['delivery_address'],
                'status' => Order::STATUS_PENDING,
                'payment_status' => Order::PAYMENT_STATUS_PENDING,
            ]);

            $this->paymentService->processPayment($order);

            if ($order->payment_status === Order::PAYMENT_STATUS_PAID) {
                $this->kitchenService->addToQueue($order);
            }

            return $order->fresh();
        });
    }

    public function updateOrderStatus(Order $order, string $status): Order
    {
        $order->update(['status' => $status]);

        if ($status === Order::STATUS_READY) {
            $this->deliveryService->assignDelivery($order);
        }

        return $order->fresh();
    }

    public function cancelOrder(Order $order): Order
    {
        if (in_array($order->status, [Order::STATUS_DELIVERED, Order::STATUS_CANCELLED])) {
            throw new \Exception('Cannot cancel order in current status');
        }

        $order->update(['status' => Order::STATUS_CANCELLED]);

        if ($order->payment_status === Order::PAYMENT_STATUS_PAID) {
            $this->paymentService->refundPayment($order);
        }

        return $order->fresh();
    }

    public function getOrderStatus(int $orderId): array
    {
        $order = Order::with(['customer', 'product'])->findOrFail($orderId);

        return [
            'id' => $order->id,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'customer' => $order->customer->name,
            'product' => $order->product->name,
            'quantity' => $order->quantity,
            'total_price' => $order->total_price,
            'created_at' => $order->created_at,
        ];
    }
}
