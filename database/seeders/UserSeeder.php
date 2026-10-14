<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::where('key', 'admin')->first();

        User::firstOrCreate(
            ['email' => 'admin@factory.local'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('ChangeMe!123'),
                'role_id' => $admin->id,
                'is_active' => true,
            ]
        );
    }
}
