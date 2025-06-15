<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function getProducts(Request $request)
    {
        // Fetch products from the database
        $products = Product::with(['category']) // eager load both relationships
        ->join('categories', 'products.category_id', '=', 'categories.id')
        ->orderBy('categories.order_num') // Order by category order
        ->orderBy('products.order_num')   // Order within each category
        ->select('products.*') // Important to avoid column name conflict
        ->where('products.hidden', '0')
        ->get();
        $products->transform(function ($product) {
            $product->subcategories = $product->subcategory(); // calls your custom method
            return $product;
        });
        return response()->json($products);
    }

    public function getCategories(Request $request)
    {
        // Fetch categories from the database
        $categories = \App\Models\Category::orderBy('order_num')
            ->get();

        // Return the categories as a JSON response
        return response()->json($categories);
    }

    public function getInventory(Request $request)
    {
        $products = Product::with(['category']) // eager load both relationships
        ->join('categories', 'products.category_id', '=', 'categories.id')
        ->orderBy('categories.order_num') // Order by category order
        ->orderBy('products.order_num')   // Order within each category
        ->select('products.*') // Important to avoid column name conflict
        ->where('products.hidden', '0')
        ->get();
        $products->transform(function ($product) {
            $product->subcategories = $product->subcategory(); // calls your custom method
            return $product;
        });

         $categories = \App\Models\Category::orderBy('order_num')
            ->with('subCategories')->get();

        // Return the inventory as a JSON response
        return response()->json([
            'products' => $products,
            'categories' => $categories
        ]);
    }
    public function stockFunc(){
        $product = Product::find(200);

        $order     = $product->approvedQuantityAfterOpenStock();
        $slr       = $product->totalSalesReturnQuantityAfterOpenStock();
        $purchase  = $product->totalPurchasedQuantityAfterOpenStock();
        $inc       = $product->totalIncreasedQuantityAfterOpenStock();
        $dec       = $product->totalDecreasedQuantityAfterOpenStock();
        $replaced  = $product->totalDamageReplacedWithOtherAfterOpenStock();

        $openStock = $product->open_stock_count;

        // Calculate new stock
        $newStockCount = $openStock + $purchase + $inc - $dec - $order - $slr - $replaced;

        // Save it
        $product->stock_count = $newStockCount;
        $product->save();
    }
}
