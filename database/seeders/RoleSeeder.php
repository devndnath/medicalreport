<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['key' => 'super_admin', 'name' => 'Super Admin (all factories)'],
            ['key' => 'admin', 'name' => 'Admin'],
            ['key' => 'store_officer', 'name' => 'Medical Room / Store Officer'],
            ['key' => 'accounts', 'name' => 'Accounts'],
            ['key' => 'management', 'name' => 'Management / Director'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['key' => $role['key']], $role);
        }
    }
}
