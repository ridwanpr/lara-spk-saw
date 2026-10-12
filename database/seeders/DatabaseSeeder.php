<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $adminRole = Role::where('name', RoleName::Admin->value)->first();
        $inspectorRole = Role::where('name', RoleName::Inspector->value)->first();
        $staffRole = Role::where('name', RoleName::Staff->value)->first();

        // Default Admin User
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role_id' => $adminRole->id,
            ]
        );

        // Default Inspector User
        User::firstOrCreate(
            ['email' => 'inspector@example.com'],
            [
                'name' => 'Inspector Sarpras',
                'password' => Hash::make('password'),
                'role_id' => $inspectorRole->id,
            ]
        );

        // Default Staff User
        User::firstOrCreate(
            ['email' => 'staff@example.com'],
            [
                'name' => 'Staff Guru',
                'password' => Hash::make('password'),
                'role_id' => $staffRole->id,
            ]
        );
    }
}
