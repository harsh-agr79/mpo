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
            $productName = trim($oldPart->product);
            $productId = $productMap[$productName] ?? null;

            DB::table('parts')->insert([
                'id' => $oldPart->id,
                'name' => $oldPart->name,
                'product_id' => $productId ? json_encode([(string) $productId]) : json_encode([]), // store id as string in JSON
                'open_balance' => $oldPart->openBalance,
                'image' => $oldPart->image ? 'parts/'.$oldPart->image : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

    }
}
