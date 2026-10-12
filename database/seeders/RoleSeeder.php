<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => RoleName::Admin->value,
                'description' => 'System Administrator with full access to master data, users, and system configuration.',
            ],
            [
                'name' => RoleName::Inspector->value,
                'description' => 'Inspector responsible for verifying reported damage, estimating costs, and ticket evaluation.',
            ],
            [
                'name' => RoleName::Staff->value,
                'description' => 'School Staff and Teachers who can report damaged assets and track repair ticket status.',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                ['description' => $role['description']]
            );
        }
    }
}
