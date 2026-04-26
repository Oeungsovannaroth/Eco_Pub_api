<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PubTable;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function index(): JsonResponse
    {
        $payments = Payment::with('order')->latest()->get();

        return response()->json([
            'message' => 'Payment list',
            'data' => $payments,
        ]);
    }

    public function store(StorePaymentRequest $request): JsonResponse
    {
        $data = $request->validated();

        $order = Order::find($data['order_id']);

        if (! $order) {
            return response()->json([
                'message' => 'Order not found',
            ], 404);
        }

        $payment = Payment::create($data);

        // Payment logic
        if ($data['payment_status'] === 'paid') {
            $order->update([
                'order_status' => 'completed',
            ]);

            $table = PubTable::find($order->table_id);
            if ($table) {
                $table->update([
                    'status' => 'available',
                ]);
            }
        }

        if ($data['payment_status'] === 'refunded') {
            $order->update([
                'order_status' => 'cancelled',
            ]);

            $orderItems = OrderItem::where('order_id', (string) $order->_id)->get();

            foreach ($orderItems as $orderItem) {
                $menuItem = MenuItem::find($orderItem->menu_item_id);

                if ($menuItem) {
                    $newStock = (int) $menuItem->stock_qty + (int) $orderItem->quantity;

                    $menuItem->update([
                        'stock_qty' => $newStock,
                        'is_available' => $newStock > 0,
                    ]);
                }
            }

            $table = PubTable::find($order->table_id);
            if ($table) {
                $table->update([
                    'status' => 'available',
                ]);
            }
        }

        return response()->json([
            'message' => 'Payment created successfully',
            'data' => $payment->load('order'),
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $payment = Payment::with('order')->findOrFail($id);

        return response()->json([
            'message' => 'Payment detail',
            'data' => $payment,
        ]);
    }

    public function update(UpdatePaymentRequest $request, string $id): JsonResponse
    {
        $payment = Payment::findOrFail($id);
        $data = $request->validated();

        $orderId = $data['order_id'] ?? $payment->order_id;
        $order = Order::find($orderId);

        if (! $order) {
            return response()->json([
                'message' => 'Order not found',
            ], 404);
        }

        $oldStatus = $payment->payment_status;

        $payment->update($data);

        $newStatus = $payment->payment_status;

        // unpaid -> paid
        if ($newStatus === 'paid' && $oldStatus !== 'paid') {
            $order->update([
                'order_status' => 'completed',
            ]);

            $table = PubTable::find($order->table_id);
            if ($table) {
                $table->update([
                    'status' => 'available',
                ]);
            }
        }

        // paid/unpaid -> refunded
        if ($newStatus === 'refunded' && $oldStatus !== 'refunded') {
            $order->update([
                'order_status' => 'cancelled',
            ]);

            $orderItems = OrderItem::where('order_id', (string) $order->_id)->get();

            foreach ($orderItems as $orderItem) {
                $menuItem = MenuItem::find($orderItem->menu_item_id);

                if ($menuItem) {
                    $newStock = (int) $menuItem->stock_qty + (int) $orderItem->quantity;

                    $menuItem->update([
                        'stock_qty' => $newStock,
                        'is_available' => $newStock > 0,
                    ]);
                }
            }

            $table = PubTable::find($order->table_id);
            if ($table) {
                $table->update([
                    'status' => 'available',
                ]);
            }
        }

        return response()->json([
            'message' => 'Payment updated successfully',
            'data' => $payment->load('order'),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();

        return response()->json([
            'message' => 'Payment deleted successfully',
        ]);
    }
}