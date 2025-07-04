<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AnalyticsController extends Controller
{
    public function mainAnalytics(Request $request){
        $startDate = $request->get( 'startDate' ) ?? getStartOfFiscalYear();
        $endDate = $request->get( 'endDate' ) ?? getEndOfFiscalYear();
        
        $customer = $request->user();
        $customerId = $customer->id;

        $orderQuery = Order::query()
        ->where( 'user_id', $customerId )
        ->where( 'mainstatus', 'approved' )
        ->whereBetween( 'date', [ $startDate, $endDate ] );

        $orderIds = $orderQuery->pluck( 'orderid' );

        $orderItems = OrderItem::whereIn( 'orderid', $orderIds )
        ->where( 'status', 'approved' )
        ->get();

        $categories = \App\Models\Category::with( 'products' )->get();

        $totalOverallSales = 0;
        $categoryStats = [];

        foreach ( $categories as $category ) {
            $categoryTotalQty = 0;
            $categoryTotalSales = 0;

            $productStats = [];

            foreach ( $category->products as $product ) {
                $items = $orderItems->where( 'product_id', $product->id );

                $productQty = $items->sum( 'approvedquantity' );
                $productSales = $items->sum( fn ( $item ) => $item->approvedquantity * $item->price );

                $categoryTotalQty += $productQty;
                $categoryTotalSales += $productSales;

                $productStats[] = [
                    'product_name' => $product->name,
                    'product_id' => $product->id,
                    'quantity' => $productQty,
                    'sales' => $productSales,
                ];
            }

            // 🔽 Sort products inside category by sales DESC
            $sortedProducts = collect( $productStats )->sortByDesc( 'sales' )->values()->all();

            $totalOverallSales += $categoryTotalSales;

            $categoryStats[] = [
                'category_name' => $category->name,
                'category_id' => $category->id,
                'total_quantity' => $categoryTotalQty,
                'total_sales' => $categoryTotalSales,
                'products' => $sortedProducts,
            ];
        }

        // 🔽 Sort categories by total sales DESC
        $sortedCategoryStats = collect( $categoryStats )->sortByDesc( 'total_sales' )->values()->all();

        return response()->json([
            'overall_sales' => $totalOverallSales,
            'categories' => $sortedCategoryStats,
        ], 200);
    }
}
