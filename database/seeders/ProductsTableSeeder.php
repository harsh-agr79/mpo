<?php

namespace Database\Seeders;

use App\Models\SubCategory;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $oldProducts = DB::table('old_products')->get();

        $subCategoryMap = DB::table('sub_categories')
            ->pluck('id', 'name')
            ->toArray();

        foreach ($oldProducts as $product) {
            $subcats = explode('|', $product->subcat ?? '');
            $subcatIds = [];

            foreach ($subcats as $subcatName) {
                $trimmed = trim($subcatName);
                if (isset($subCategoryMap[$trimmed])) {
                    $subcatIds[] = (string) $subCategoryMap[$trimmed];
                }
            }

            DB::table('products')->insert([
                'id' => $product->id,
                'name' => $product->name,
                'category_id' => $product->category_id,
                'order_num' => $product->ordernum,
                'stock' => strtolower($product->stock) === 'on' ? 1 : 0,
                'hidden' => strtolower($product->hide) === 'on' ? 1 : 0,
                'price' => $product->price,
                'details' => substr($product->details, 0, 65535),
                'offer' => $product->offer,
                'image' => 'products/' . $product->img,
                'image_2' => 'products/' . $product->img2,
                'prod_unique_id' => $product->produni_id,
                'sub_category_id' => json_encode($subcatIds),
                'deleted_at' => $product->deleted_at,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ]);
        }
    }
}
