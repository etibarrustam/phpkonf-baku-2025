<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function health()
    {
        return response()->json([
            'service' => 'payment-service',
            'status' => 'healthy',
            'timestamp' => time()
        ]);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|integer',
            'amount' => 'required|numeric|min:0'
        ]);

        $payment = Payment::create([
            'order_id' => $validated['order_id'],
            'amount' => $validated['amount'],
            'status' => 'completed',
            'payment_method' => 'credit_card',
            'transaction_id' => 'TXN' . time() . rand(1000, 9999)
        ]);

        return response()->json($payment, 201);
    }

    public function show($id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        return response()->json($payment);
    }

    public function index()
    {
        return response()->json(Payment::all());
    }
}
