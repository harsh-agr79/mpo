<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderMaterial;
use Carbon\Carbon;

class CartController extends Controller
{
     public function updateOrRemove(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:0',
        ]);

        $user = $request->user();
        $productId = $request->product_id;
        $quantity = $request->quantity;

        $cartItem = CartItem::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if (is_null($quantity) || $quantity == 0) {
            if ($cartItem) {
                $cartItem->delete();
            }
        } else {
            if ($cartItem) {
                $cartItem->update(['quantity' => $quantity]);
            } else {
                CartItem::create([
                    'user_id' => $user->id,
                    'product_id' => $productId,
                    'quantity' => $quantity
                ]);
            }
        }

        // Fetch full cart details
        $cartItems = CartItem::with('product')
            ->where('user_id', $user->id)
            ->get();

        $cart = $cartItems->map(function ($item) {
            $subtotal = $item->quantity * $item->product->price;
            return [
                'product_id' => $item->product_id,
                'name'       => $item->product->name,
                'price'      => $item->product->price,
                'quantity'   => $item->quantity,
                'subtotal'   => $subtotal,
            ];
        });

        $total = $cart->sum('subtotal');

        return response()->json([
            'cart' => $cart,
            'total' => $total,
        ]);
    }

    public function getCart(Request $request)
    {
        $user = $request->user();

        $cartItems = CartItem::with('product')
            ->where('user_id', $user->id)
            ->get();

        $cart = $cartItems->map(function ($item) {
            $subtotal = $item->quantity * $item->product->price;
            return [
                'product_id' => $item->product_id,
                'name'       => $item->product->name,
                'price'      => $item->product->price,
                'quantity'   => $item->quantity,
                'subtotal'   => $subtotal,
            ];
        });

        $total = $cart->sum('subtotal');

        return response()->json([
            'cart' => $cart,
            'total' => $total,
        ]);
    }

   public function checkout(Request $request)
    {
        $user = $request->user();

        // Get all cart items for the user
        $cartItems = CartItem::where('user_id', $user->id)->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        // Calculate total
        $total = 0;
        foreach ($cartItems as $item) {
            $product = \App\Models\Product::find($item->product_id);
            if (!$product) {
                return response()->json(['message' => "Product not found for ID {$item->product_id}"], 404);
            }
            $total += $product->price * $item->quantity;
        }

        // Create Order
        $order = Order::create([
            'user_id' => $user->id,
            'orderid' => time() . $user->id,
            'mainstatus' => 'pending',
            'date' => now()->toDateTimeString(),
            'save' => $request->input('save', false),
            'total' => $total,
            'net_total' => $total,
            'nepmonth' => getNepaliMonth(now()),
            'nepyear' => getNepaliYear(now()),
        ]);

        // Create Order Items
        foreach ($cartItems as $item) {
            $product = \App\Models\Product::find($item->product_id);
            $offers = $product->offer ?? [];

            $quantity = $item->quantity;
            $matchedOffer = [];

            // Find the best matched offer
            if (!empty($offers)) {
                $sortedKeys = collect($offers)
                    ->keys()
                    ->map(fn($key) => (int) $key)
                    ->sortDesc()
                    ->values();

                foreach ($sortedKeys as $key) {
                    if ($quantity >= $key) {
                        $matchedOffer = [$key => $offers[$key]];
                        break;
                    }
                }
            }

            OrderItem::create([
                'orderid' => $order->orderid,
                'product_id' => $item->product_id,
                'offer' => json_encode($matchedOffer),
                'price' => $product->price,
                'actualprice' => $product->price,
                'quantity' => $quantity,
                'approvedquantity' => 0,
                'status' => 'pending',
            ]);
        }
        CartItem::where('user_id', $user->id)->delete();
        return response()->json(['message' => 'Order placed successfully', 'order_id' => $order->orderid]);
    }

    public function getConfirmedOrders(Request $request)
    {
        $user = $request->user();

        $orders = Order::where('user_id', $user->id)
            ->where('save', false)
            ->orderBy('date', 'desc')
            ->get();

        return response()->json($orders);
    }

    public function getSavedOrders(Request $request)
    {
        $user = $request->user();

        $orders = Order::where('user_id', $user->id)
            ->where('save', true)
            ->orderBy('date', 'desc')
            ->get();

        return response()->json($orders);
    }

    public function getOrderDetails(Request $request, $id)
    {
        // try {
        //     $user = $request->user();

        //     if (!$user) {
        //         return response()->json(['message' => 'Unauthorized user'], 401);
        //     }

        //     // Check if the order exists for this user
        //     $order = Order::where('orderid', $id)
        //         ->where('user_id', $user->id)
        //         ->first();

        //     if (!$order) {
        //         return response()->json(['message' => 'Order not found or access denied'], 404);
        //     }

        //     // Fetch order items with product relation
        //     $orderItems = OrderItem::where('orderid', $id)
        //         ->with('product')
        //         ->get();

        //     if ($orderItems->isEmpty()) {
        //         // Optional: not an error, but you may want to notify
        //         \Log::info("Order $id has no items for user " . $user->id);
        //     }

        //     // Fetch order materials with material relation
        //     $orderMaterials = OrderMaterial::where('orderid', $id)
        //         ->with('material')
        //         ->get();

        //     if ($orderMaterials->isEmpty()) {
        //         // Optional: not an error, but loggable
        //         \Log::info("Order $id has no materials for user " . $user->id);
        //     }

        //     return response()->json([
        //         'order' => $order,
        //         'items' => $orderItems,
        //         'materials' => $orderMaterials,
        //     ]);
        // } catch (\Exception $e) {
        //     \Log::error("Error fetching order $id: " . $e->getMessage());

        //     return response()->json([
        //         'message' => 'An error occurred while fetching order details',
        //         'error' => config('app.debug') ? $e->getMessage() : null
        //     ], 500);
        // }
        return response()->json([
            'message' => 'This endpoint is not implemented yet.'
        ], 200);
    }


}
