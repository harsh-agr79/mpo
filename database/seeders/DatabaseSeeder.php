<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Role;
use App\Models\Admin;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = Admin::factory()->create([
            'name' => 'Harsh',
            'email' => 'agrharsh7932@gmail.com',
        ]);
        $role = Role::create(['name' => 'Admin']);
        $user->assignRole($role);
    }
}
