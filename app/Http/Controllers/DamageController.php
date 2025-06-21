<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Damage; // Assuming you have a Damage model
use App\Models\Product;
use App\Models\DamageItem;
use App\Models\DamageItemDetail;

class DamageController extends Controller
{
    public function createDamage(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.remarks' => 'nullable|string',
        ]);

        $user = $request->user();
        $items = $request->input('items');
        $invoice = 'INV-' . now()->format('YmdHis') . '-' . $user->id;

        Damage::create([
            'user_id' => $user->id,
            'date' => now(),
            'invoice_id'=> $invoice,
            'mainstatus' => 'pending',
        ]);

        foreach ($items as $item) {
            $productId = $item['product_id'];
            $quantity = $item['quantity'];
            $remarks = $item['remarks'] ?? null;

            DamageItem::create([
                'invoice_id' => $invoice,
                'product_id' => $productId,
                'quantity' => $quantity,
                'cusremarks' => $remarks,
                'instatus' => 'pending',
            ]);
        }

        return response()->json(['message' => 'Processed successfully']);
    }
}
