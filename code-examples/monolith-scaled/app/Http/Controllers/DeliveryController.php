<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;

class DeliveryController extends Controller
{
    public function active(): JsonResponse
    {
        $orders = Order::where('status', 'delivering')
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
