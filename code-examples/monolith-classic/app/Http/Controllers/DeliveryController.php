<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\DeliveryService;
use Illuminate\Http\JsonResponse;

class DeliveryController extends Controller
{
    public function __construct(
        private DeliveryService $deliveryService
    ) {}

    public function activeDeliveries(): JsonResponse
    {
        $deliveries = $this->deliveryService->getActiveDeliveries();

        return response()->json([
            'success' => true,
            'data' => $deliveries
        ]);
    }

    public function markDelivered(int $id): JsonResponse
    {
        try {
            $order = Order::findOrFail($id);
            $this->deliveryService->markAsDelivered($order);

            return response()->json([
                'success' => true,
                'message' => 'Order marked as delivered'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
