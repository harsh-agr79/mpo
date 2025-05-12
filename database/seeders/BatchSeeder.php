<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BatchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $oldBatches = DB::table('batch')->get();

        foreach ($oldBatches as $old) {
            // Find product by prod_unique_id
            $product = DB::table('products')->where('prod_unique_id', $old->produni_id)->first();

            // If product not found, skip this batch
            if (!$product) {
                continue;
            }

            DB::table('batches')->insert([
                'id' => $old->id,
                'batch_no' => $old->batch,
                'product_id' => $product->id,
            ]);
        }
    }
}
