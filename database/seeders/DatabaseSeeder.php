<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        User::factory()->create([
            'id' => 1,
            'name' => 'Administrador',
            'email' => 'admin@mail.com',
            'password' => bcrypt('admin'), 
            'is_admin' => true,
        ]);
    }
}
