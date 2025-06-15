<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;

class UpdateStockCount extends Command
{
    protected $signature = 'stock:update';
    protected $description = 'Update stock count for all products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating stock counts...');

        $products = Product::all();

        foreach ($products as $product) {
            if (is_null($product->open_stock_date)) {
                continue;
            }

            $order     = $product->approvedQuantityAfterOpenStock();
            $slr       = $product->totalSalesReturnQuantityAfterOpenStock();
            $purchase  = $product->totalPurchasedQuantityAfterOpenStock();
            $inc       = $product->totalIncreasedQuantityAfterOpenStock();
            $dec       = $product->totalDecreasedQuantityAfterOpenStock();
            $replaced  = $product->totalDamageReplacedWithOtherAfterOpenStock();

            $openStock = $product->open_stock_count;

            $newStockCount = $openStock + $purchase + $inc - $dec - $order - $slr - $replaced;

            $product->stock_count = $newStockCount;
            $product->save();
        }

        $this->info('Stock counts updated successfully.');
    }
}
