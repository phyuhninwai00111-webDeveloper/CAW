<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['id' => 1, 'role_name' => 'HR'],
            ['id' => 2, 'role_name' => 'HOD'],
            ['id' => 3, 'role_name' => 'Staff'],
        ];
        foreach ($roles as $r) {
            DB::table('roles')->updateOrInsert(['id' => $r['id']], ['role_name' => $r['role_name']]);
        }
    }
}
