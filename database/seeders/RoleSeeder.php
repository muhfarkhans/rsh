<?php

namespace Database\Seeders;

use App\Constants\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleNames = [Role::THERAPIST, Role::CASHIER];

        foreach ($roleNames as $key => $role) {
            $exists = DB::table('roles')->where('name', $role)->exists();

            if (!$exists) {
                DB::table('roles')->insert([
                    'name' => $role,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
