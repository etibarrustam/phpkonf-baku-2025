<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function health()
    {
        return response()->json([
            'service' => 'delivery-service',
            'status' => 'healthy',
            'timestamp' => time()
        ]);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|integer',
            'customer_name' => 'required|string'
        ]);

        $drivers = ['John Doe', 'Jane Smith', 'Mike Johnson', 'Sarah Williams'];

        $delivery = Delivery::create([
            'order_id' => $validated['order_id'],
            'customer_name' => $validated['customer_name'],
            'driver_name' => $drivers[array_rand($drivers)],
            'status' => 'assigned',
            'estimated_time' => rand(15, 45)
        ]);

        return response()->json($delivery, 201);
    }

    public function show($id)
    {
        $delivery = Delivery::find($id);

        if (!$delivery) {
            return response()->json(['error' => 'Delivery not found'], 404);
        }

        return response()->json($delivery);
    }

    public function index()
    {
        return response()->json(Delivery::all());
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:assigned,picked_up,in_transit,delivered'
        ]);

        $delivery = Delivery::find($id);

        if (!$delivery) {
            return response()->json(['error' => 'Delivery not found'], 404);
        }

        $delivery->update(['status' => $validated['status']]);

        return response()->json($delivery);
    }
}
