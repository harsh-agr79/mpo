<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $oldadmins = DB::table('old_admins')->get();

        foreach ($oldadmins as $oldadmin) {
            DB::table('admins')->insert([
                // 'id' => $oldadmin->id,
                'name' => $oldadmin->email,
                'email' => $oldadmin->email.'@gmail.com',
                'password' => $oldadmin->password,
                // 'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
