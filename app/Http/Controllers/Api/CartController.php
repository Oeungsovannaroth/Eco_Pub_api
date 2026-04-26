<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Http\Requests\CheckoutCartRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PubTable;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
class CartController extends Controller
{
    protected function getOrCreateActiveCart(string $userId): Cart
    {
        $cart = Cart::where('user_id', $userId)
            ->where('status', 'active')
            ->first();

        if (!$cart) {
            $cart = Cart::create([
                'user_id' => $userId,
                'status'  => 'active',
            ]);
        }

        return $cart;
    }

    public function myCart(): JsonResponse
{
    $user = auth::user();

    $cart = Cart::with('items.menuItem')
        ->where('user_id', (string) $user->_id)
        ->where('status', 'active')
        ->first();

    if (! $cart) {
        return response()->json([
            'message' => 'Cart is empty',
            'data' => [
                'items' => [],
                'total' => 0,
            ],
        ]);
    }

    $total = $cart->items->sum('subtotal');

    return response()->json([
        'message' => 'Cart retrieved successfully',
        'data' => [
            'cart' => $cart,
            'items' => $cart->items,
            'total' => $total,
        ],
    ]);
}

    public function add(AddToCartRequest $request): JsonResponse
    {
        $user = auth::user();
        $data = $request->validated();

        $menuItem = MenuItem::find($data['menu_item_id']);

        if (! $menuItem) {
            return response()->json([
                'message' => 'Menu item not found',
            ], 404);
        }

        if (! $menuItem->is_available || $menuItem->status !== 'active') {
            return response()->json([
                'message' => 'This item is not available',
            ], 422);
        }

        if ((int) $menuItem->stock_qty < (int) $data['quantity']) {
            return response()->json([
                'message' => 'Not enough stock available',
            ], 422);
        }

        $cart = $this->getOrCreateActiveCart((string) $user->_id);

        $existingItem = CartItem::where('cart_id', (string) $cart->_id)
            ->where('menu_item_id', (string) $menuItem->_id)
            ->first();

        if ($existingItem) {
            $newQty = $existingItem->quantity + (int) $data['quantity'];

            if ((int) $menuItem->stock_qty < $newQty) {
                return response()->json([
                    'message' => 'Requested quantity exceeds stock',
                ], 422);
            }

            $existingItem->update([
                'quantity' => $newQty,
                'price' => (float) $menuItem->price,
                'subtotal' => $newQty * (float) $menuItem->price,
            ]);
        } else {
            CartItem::create([
                'cart_id' => (string) $cart->_id,
                'menu_item_id' => (string) $menuItem->_id,
                'quantity' => (int) $data['quantity'],
                'price' => (float) $menuItem->price,
                'subtotal' => (int) $data['quantity'] * (float) $menuItem->price,
            ]);
        }

        return $this->myCart();
    }

    public function updateItem(string $id, UpdateCartItemRequest $request): JsonResponse
    {
        $user = auth::user();
        $cart = $this->getOrCreateActiveCart((string) $user->_id);

        $cartItem = CartItem::where('cart_id', (string) $cart->_id)
            ->where('_id', $id)
            ->first();

        if (! $cartItem) {
            return response()->json([
                'message' => 'Cart item not found',
            ], 404);
        }

        $menuItem = MenuItem::find($cartItem->menu_item_id);

        if (! $menuItem) {
            return response()->json([
                'message' => 'Menu item not found',
            ], 404);
        }

        $quantity = (int) $request->validated()['quantity'];

        if ((int) $menuItem->stock_qty < $quantity) {
            return response()->json([
                'message' => 'Not enough stock available',
            ], 422);
        }

        $cartItem->update([
            'quantity' => $quantity,
            'price' => (float) $menuItem->price,
            'subtotal' => $quantity * (float) $menuItem->price,
        ]);

        return $this->myCart();
    }

    public function removeItem(string $id): JsonResponse
    {
        $user = auth::user();
        $cart = $this->getOrCreateActiveCart((string) $user->_id);

        $cartItem = CartItem::where('cart_id', (string) $cart->_id)
            ->where('_id', $id)
            ->first();

        if (! $cartItem) {
            return response()->json([
                'message' => 'Cart item not found',
            ], 404);
        }

        $cartItem->delete();

        return $this->myCart();
    }

    public function clear(): JsonResponse
    {
        $user = auth::user();

        $cart = Cart::where('user_id', (string) $user->_id)
            ->where('status', 'active')
            ->first();

        if ($cart) {
            CartItem::where('cart_id', (string) $cart->_id)->delete();
        }

        return response()->json([
            'message' => 'Cart cleared successfully',
        ]);
    }

    public function checkout(CheckoutCartRequest $request): JsonResponse
{
    $user = auth::user();
    $data = $request->validated();

    $cart = Cart::with('items.menuItem')
        ->where('user_id', (string) $user->_id)
        ->where('status', 'active')
        ->first();

    if (! $cart || $cart->items->isEmpty()) {
        return response()->json([
            'message' => 'Cart is empty',
        ], 422);
    }

    // Find table by table_number instead of _id
    $table = PubTable::where('table_number', strtoupper(trim($data['table_id'])))->first();

    if (! $table) {
        return response()->json([
            'message' => 'Table number not found',
        ], 404);
    }

    if (! empty($data['reservation_id']) && ! Reservation::find($data['reservation_id'])) {
        return response()->json([
            'message' => 'Reservation not found',
        ], 404);
    }

    $totalAmount = 0;

    foreach ($cart->items as $item) {
        $menuItem = MenuItem::find($item->menu_item_id);

        if (! $menuItem) {
            return response()->json([
                'message' => 'Menu item not found during checkout',
            ], 404);
        }

        if ((int) $menuItem->stock_qty < (int) $item->quantity) {
            return response()->json([
                'message' => "Not enough stock for {$menuItem->name}",
            ], 422);
        }

        $totalAmount += (float) $item->subtotal;
    }

    $order = Order::create([
        'user_id' => (string) $user->_id,
        'table_id' => (string) $table->_id, // save real table id in DB
        'reservation_id' => $data['reservation_id'] ?? null,
        'order_no' => 'ORD-' . strtoupper(\Illuminate\Support\Str::random(8)),
        'order_type' => $data['order_type'],
        'total_amount' => $totalAmount,
        'order_status' => 'pending',
        'note' => $data['note'] ?? null,
    ]);

    foreach ($cart->items as $item) {
        OrderItem::create([
            'order_id' => (string) $order->_id,
            'menu_item_id' => (string) $item->menu_item_id,
            'quantity' => (int) $item->quantity,
            'price' => (float) $item->price,
            'subtotal' => (float) $item->subtotal,
            'note' => null,
        ]);

        $menuItem = MenuItem::find($item->menu_item_id);

        if ($menuItem) {
            $newStock = (int) $menuItem->stock_qty - (int) $item->quantity;

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

    CartItem::where('cart_id', (string) $cart->_id)->delete();

    $cart->update([
        'status' => 'checked_out',
    ]);

    return response()->json([
        'message' => 'Checkout successful',
        'data' => $order,
    ], 201);
}
}