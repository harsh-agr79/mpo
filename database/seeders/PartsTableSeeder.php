<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PartsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $oldParts = DB::table('old_parts')->get();

        // Map product names to their numeric IDs
        $productMap = DB::table('products')
            ->pluck('id', 'name') // [name => id]
            ->toArray();

        foreach ($oldParts as $oldPart) {
            $prods = explode('|', $oldPart->product ?? '');
            $prodIds = [];

            foreach ($prods as $prodName) {
                $trimmed = trim($prodName);
                if (isset($productMap[$trimmed])) {
                    $prodIds[] = (string) $productMap[$trimmed];
                }
            }

            DB::table('parts')->insert([
                'id' => $oldPart->id,
                'name' => $oldPart->name,
                'product_id' => json_encode($prodIds),
                'open_balance' => $oldPart->openBalance,
                'image' => $oldPart->image ? 'parts/' . $oldPart->image : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

    }
}
