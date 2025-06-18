<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CartItem;
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

    public function checkout(Request $request){
        $user = $request->user();
        $orderid = time().$user->id;
        $order = Order::create([
            'user_id' => $user->id,
            'orderid' => $orderid,
            'mainstatus' => 'pending',
            'date' => now()->toDateTimeString(),
            'save' => $request->save ?? false,
            'total' => $this->getCartTotal(),
            'net_total' => $this->getCartTotal(),
            'nepmonth' => getNepaliMonth(now()),
            'nepyear' => getNepaliYear(now()),
        ]);
        $cartItems = CartItem::where('user_id', $user->id)->get();

        foreach($cartItems as $item){
                $product = \App\Models\Product::find($item->product_id);
                $offers = $product->offer;
                $quantity = $item->quantity;

                // Initialize empty matched offer
                $matchedOffer = [];

                if (!empty($offers)) {
                    // Convert keys to integers and sort descending
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
                    'quantity' => $item->quantity,
                    'approvedquantity' => 0,
                    'status' => 'pending'
                ]);
        }
    }   
}
