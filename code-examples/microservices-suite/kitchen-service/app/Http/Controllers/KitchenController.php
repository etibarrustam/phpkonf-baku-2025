<?php

namespace App\Http\Controllers;

use App\Models\KitchenOrder;
use Illuminate\Http\Request;

class KitchenController extends Controller
{
    public function health()
    {
        return response()->json([
            'service' => 'kitchen-service',
            'status' => 'healthy',
            'timestamp' => time()
        ]);
    }

    public function prepare(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|integer',
            'items' => 'required|array'
        ]);

        $kitchenOrder = KitchenOrder::create([
            'order_id' => $validated['order_id'],
            'items' => $validated['items'],
            'status' => 'preparing',
            'preparation_time' => rand(10, 30)
        ]);

        return response()->json($kitchenOrder, 201);
    }

    public function show($id)
    {
        $kitchenOrder = KitchenOrder::find($id);

        if (!$kitchenOrder) {
            return response()->json(['error' => 'Kitchen order not found'], 404);
        }

        return response()->json($kitchenOrder);
    }

    public function index()
    {
        return response()->json(KitchenOrder::all());
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:preparing,ready,completed'
        ]);

        $kitchenOrder = KitchenOrder::find($id);

        if (!$kitchenOrder) {
            return response()->json(['error' => 'Kitchen order not found'], 404);
        }

        $kitchenOrder->update(['status' => $validated['status']]);

        return response()->json($kitchenOrder);
    }
}
