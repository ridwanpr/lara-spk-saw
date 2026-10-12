<?php

namespace Database\Factories;

use App\Enums\AssetCondition;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asset>
 */
class AssetFactory extends Factory
{
    protected $model = Asset::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('AST-#####')),
            'name' => ucfirst(fake()->words(3, true)),
            'category_id' => AssetCategory::factory(),
            'room_id' => Room::factory(),
            'procurement_year' => fake()->numberBetween(2018, (int) date('Y')),
            'condition' => fake()->randomElement(AssetCondition::cases()),
        ];
    }
}
