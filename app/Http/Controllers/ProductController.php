<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function getProducts(Request $request)
    {
        // Fetch products from the database
        $products = Product::with(['category', 'subCategory']) // eager load both relationships
        ->join('categories', 'products.category_id', '=', 'categories.id')
        ->orderBy('categories.order_num') // Order by category order
        ->orderBy('products.order_num')   // Order within each category
        ->select('products.*') // Important to avoid column name conflict
        ->where('products.hidden', '0')
        ->get();

        // Return the products as a JSON response
        return response()->json($products);
    }
}
