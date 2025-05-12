<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProblemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $oldProblems = DB::table('problem')->get();

        foreach ($oldProblems as $old) {
            DB::table(table: 'problems')->insert([
                'id' => $old->id,
                'problem' => $old->problem,
                'category_id' => json_encode([(string) $old->category_id]),
            ]);
        }
    }
}
