<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('settings')->truncate();
        DB::table('settings')->insert([
            'additional_cupping_price' => 15000,
            'limit_cupping_point' => 14,
            'updated_by' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
