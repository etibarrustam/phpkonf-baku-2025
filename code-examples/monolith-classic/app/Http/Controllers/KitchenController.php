<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\KitchenService;
use Illuminate\Http\JsonResponse;

class KitchenController extends Controller
{
    public function __construct(
        private KitchenService $kitchenService
    ) {}

    public function queue(): JsonResponse
    {
        $queue = $this->kitchenService->getKitchenQueue();

        return response()->json([
            'success' => true,
            'data' => $queue
        ]);
    }

    public function markReady(int $id): JsonResponse
    {
        try {
            $order = Order::findOrFail($id);
            $this->kitchenService->markAsReady($order);

            return response()->json([
                'success' => true,
                'message' => 'Order marked as ready'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
