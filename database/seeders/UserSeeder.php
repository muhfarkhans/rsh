<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@admin.admin',
            'password' => bcrypt('password'),
            'phone' => '081237121212',
            'address' => 'Bantul Yogyakarta',
            'is_active' => 1,
        ]);
    }
}
