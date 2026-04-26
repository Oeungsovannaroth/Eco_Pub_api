<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PubTable;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(): JsonResponse
    {
        $orders = Order::with(['user', 'table', 'reservation', 'items'])->latest()->get();

        return response()->json([
            'message' => 'Order list',
            'data' => $orders,
        ]);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $data = $request->validated();

        
        $data['reservation_id'] = $data['reservation_id'] ?? null;
        if ($data['reservation_id'] === '') {
            $data['reservation_id'] = null;
        }

        if (! User::find($data['user_id'])) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $table = PubTable::find($data['table_id']);
        if (! $table) {
            return response()->json(['message' => 'Table not found'], 404);
        }

        if ($data['reservation_id'] !== null && ! Reservation::find($data['reservation_id'])) {
            return response()->json(['message' => 'Reservation not found'], 404);
        }

        $totalAmount = 0;
        $preparedItems = [];

        foreach ($data['items'] as $item) {
            $menuItem = MenuItem::find($item['menu_item_id']);

            if (! $menuItem) {
                return response()->json([
                    'message' => 'Menu item not found: ' . $item['menu_item_id'],
                ], 404);
            }

            $quantity = (int) $item['quantity'];

            if (! $menuItem->is_available) {
                return response()->json([
                    'message' => "{$menuItem->name} is not available.",
                ], 422);
            }

            if ((int) $menuItem->stock_qty < $quantity) {
                return response()->json([
                    'message' => "Not enough stock for {$menuItem->name}. Available: {$menuItem->stock_qty}",
                ], 422);
            }

            $price = (float) $menuItem->price;
            $subtotal = $price * $quantity;
            $totalAmount += $subtotal;

            $preparedItems[] = [
                'menu_item_id' => (string) $menuItem->_id,
                'quantity' => $quantity,
                'price' => $price,
                'subtotal' => $subtotal,
                'note' => $item['note'] ?? null,
            ];
        }

        $order = Order::create([
            'user_id' => $data['user_id'],
            'table_id' => $data['table_id'],
            'reservation_id' => $data['reservation_id'],
            'order_no' => 'ORD-' . strtoupper(Str::random(8)),
            'order_type' => $data['order_type'],
            'total_amount' => $totalAmount,
            'order_status' => $data['order_status'],
            'note' => $data['note'] ?? null,
        ]);

        foreach ($preparedItems as $preparedItem) {
            OrderItem::create([
                'order_id' => (string) $order->_id,
                'menu_item_id' => $preparedItem['menu_item_id'],
                'quantity' => $preparedItem['quantity'],
                'price' => $preparedItem['price'],
                'subtotal' => $preparedItem['subtotal'],
                'note' => $preparedItem['note'],
            ]);

            $menuItem = MenuItem::find($preparedItem['menu_item_id']);

            if ($menuItem) {
                $newStock = (int) $menuItem->stock_qty - (int) $preparedItem['quantity'];

                $menuItem->update([
                    'stock_qty' => $newStock,
                    'is_available' => $newStock > 0,
                ]);
            }
        }

        if (in_array($data['order_type'], ['dine_in', 'reservation'])) {
            $table->update([
                'status' => 'occupied',
            ]);
        }

        $order->load(['user', 'table', 'reservation', 'items']);

        return response()->json([
            'message' => 'Order created successfully',
            'data' => $order,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $order = Order::with(['user', 'table', 'reservation', 'items'])->findOrFail($id);

        return response()->json([
            'message' => 'Order detail',
            'data' => $order,
        ]);
    }

    public function update(UpdateOrderRequest $request, string $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $data = $request->validated();

        $data['reservation_id'] = $data['reservation_id'] ?? $order->reservation_id;
        if ($data['reservation_id'] === '') {
            $data['reservation_id'] = null;
        }

        if (isset($data['user_id']) && ! User::find($data['user_id'])) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if (isset($data['table_id']) && ! PubTable::find($data['table_id'])) {
            return response()->json(['message' => 'Table not found'], 404);
        }

        if ($data['reservation_id'] !== null && ! Reservation::find($data['reservation_id'])) {
            return response()->json(['message' => 'Reservation not found'], 404);
        }

        $oldStatus = $order->order_status;

        $order->update($data);

        if (
            isset($data['order_status']) &&
            $data['order_status'] === 'cancelled' &&
            $oldStatus !== 'cancelled'
        ) {
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
            'message' => 'Order updated successfully',
            'data' => $order->load(['user', 'table', 'reservation', 'items']),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $order = Order::findOrFail($id);

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

        OrderItem::where('order_id', (string) $order->_id)->delete();

        $table = PubTable::find($order->table_id);
        if ($table) {
            $table->update([
                'status' => 'available',
            ]);
        }

        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully',
        ]);
    }

    public function updateStatus(string $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $status = request('status');

        if (! in_array($status, ['pending', 'preparing', 'served', 'completed', 'cancelled'])) {
            return response()->json(['message' => 'Invalid order status'], 422);
        }

        $oldStatus = $order->order_status;

        $order->update([
            'order_status' => $status,
        ]);

        if ($status === 'cancelled' && $oldStatus !== 'cancelled') {
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

        if (in_array($status, ['pending', 'preparing', 'served'])) {
            $table = PubTable::find($order->table_id);
            if ($table) {
                $table->update([
                    'status' => 'occupied',
                ]);
            }
        }

        if ($status === 'completed') {
            $table = PubTable::find($order->table_id);
            if ($table) {
                $table->update([
                    'status' => 'available',
                ]);
            }
        }

        return response()->json([
            'message' => 'Order status updated successfully',
            'data' => $order,
        ]);
    }
}