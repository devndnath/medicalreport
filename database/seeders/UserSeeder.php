<?php

namespace Database\Seeders;

use App\Models\Factory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = Role::where('key', 'super_admin')->first();
        $admin = Role::where('key', 'admin')->first();

        // One Super Admin, tied to no single factory — switches between both.
        User::firstOrCreate(
            ['email' => 'superadmin@factory.local'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('ChangeMe!123'),
                'role_id' => $superAdmin->id,
                'factory_id' => null,
                'is_active' => true,
            ]
        );

        // One factory-bound Admin per factory, so each factory can be run
        // independently from the start.
        foreach (Factory::orderBy('id')->get() as $factory) {
            $slug = str($factory->code)->lower();

            User::firstOrCreate(
                ['email' => "admin.{$slug}@factory.local"],
                [
                    'name' => "Admin — {$factory->name}",
                    'password' => Hash::make('ChangeMe!123'),
                    'role_id' => $admin->id,
                    'factory_id' => $factory->id,
                    'is_active' => true,
                ]
            );
        }
    }
}
