<?php

namespace Database\Seeders;

use App\Models\Factory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    /**
     * One user for each remaining role in every factory, so each screen can
     * be tried as the person who would really use it. UserSeeder already
     * creates the Super Admin and one Admin per factory.
     */
    public function run(): void
    {
        $roles = [
            'store_officer' => ['store', 'Medical Officer'],
            'accounts' => ['accounts', 'Accounts'],
            'management' => ['management', 'Management'],
        ];

        foreach (Factory::orderBy('id')->get() as $factory) {
            $slug = str($factory->code)->lower();

            foreach ($roles as $key => [$prefix, $label]) {
                $role = Role::where('key', $key)->first();
                if (! $role) {
                    continue;
                }

                User::firstOrCreate(
                    ['email' => "{$prefix}.{$slug}@factory.local"],
                    [
                        'name' => "{$label} — {$factory->name}",
                        'password' => Hash::make('ChangeMe!123'),
                        'role_id' => $role->id,
                        'factory_id' => $factory->id,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
