<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function getProducts(Request $request)
    {
        // Fetch products from the database
        $products = Product::with('category')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->orderBy('categories.order_num') // Order by category order
            ->orderBy('products.order_num')   // Order within each category
            ->select('products.*') // Important: select only product fields to avoid column conflicts
            ->where('products.hidden', '0')
            ->get();

        // Return the products as a JSON response
        return response()->json($products);
    }
}
