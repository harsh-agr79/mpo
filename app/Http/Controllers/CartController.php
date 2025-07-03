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
        $user = $request->user();

        $order = Order::where('orderid', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $orderItems = OrderItem::where('orderid', $id)->with('product')->get();
        $orderMaterials = OrderMaterial::where('orderid', $id)->with('material')->get();

        $items = OrderItem::where('orderid', $id)->with('product.category')->get();

        $grouped = $items->groupBy(fn ($item) => $item->product->category->name ?? 'Uncategorized');

        $categoryCounts = $grouped->map(fn ($group) => $group->count());

        $categoryApprovedSums = $grouped->map(fn ($group) => $group->sum('approvedquantity'));

        $categoryApprovedValueSums = $grouped->map(fn ($group) =>
            $group->sum(fn ($item) => ($item->approvedquantity ?? 0) * ($item->price ?? 0))
        );

        // Totals
        $totalItems = $items->count();
        $totalApprovedQuantity = $items->sum('approvedquantity');
        $totalApprovedValue = $items->sum(fn ($item) => ($item->approvedquantity ?? 0) * ($item->price ?? 0));

        $discount = $this->record->discount ?? 0;
        $finalTotal = $totalApprovedValue - (($discount/100) * $totalApprovedValue);

         $totalBenefit = $items->sum(function ($item) {
            $actualPrice = $item->actualprice ?? 0;
            $price = $item->price ?? 0;
            $approvedQty = $item->approvedquantity ?? 0;
            return ($actualPrice > $price) ? ($actualPrice - $price) * $approvedQty : 0;
        });

        return response()->json([
            'order' => $order,
            'miti' => getNepaliDate($order->date),
            'items' => $orderItems,
            'materials' => $orderMaterials,
            'categoryCounts' => $categoryCounts,
            'categoryApprovedSums' => $categoryApprovedSums,
            'categoryApprovedValueSums' => $categoryApprovedValueSums,
            'totalItems' => $totalItems,
            'totalApprovedQuantity' => $totalApprovedQuantity,
            'totalApprovedValue' => $totalApprovedValue,
            'discount' => $discount,
            'finalTotal' => $finalTotal,
            'totalBenefit' => $totalBenefit,
        ]);
    }

}
