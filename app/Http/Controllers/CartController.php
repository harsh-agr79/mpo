<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CartItem;

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
}
