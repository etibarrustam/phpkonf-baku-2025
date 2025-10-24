<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;

class KitchenController extends Controller
{
    public function queue(): JsonResponse
    {
        $orders = Order::where('status', 'preparing')
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders,
            'count' => $orders->count(),
            'instance' => getenv('INSTANCE_ID') ?: 'unknown',
        ]);
    }
}
