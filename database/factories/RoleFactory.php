<?php

namespace Database\Factories;

use App\Enums\RoleName;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(RoleName::cases()),
            'description' => fake()->sentence(),
        ];
    }

    /**
     * Set role to Admin.
     */
    public function admin(): static
    {
        return $this->state(fn () => [
            'name' => RoleName::Admin,
            'description' => 'Administrator with full system privileges',
        ]);
    }

    /**
     * Set role to Inspector.
     */
    public function inspector(): static
    {
        return $this->state(fn () => [
            'name' => RoleName::Inspector,
            'description' => 'Inspector / Facilities verifier',
        ]);
    }

    /**
     * Set role to Staff.
     */
    public function staff(): static
    {
        return $this->state(fn () => [
            'name' => RoleName::Staff,
            'description' => 'Staff / Teacher asset reporter',
        ]);
    }
}
