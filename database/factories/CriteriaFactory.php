<?php

namespace Database\Factories;

use App\Enums\CriteriaInputType;
use App\Enums\CriteriaType;
use App\Models\Criteria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Criteria>
 */
class CriteriaFactory extends Factory
{
    protected $model = Criteria::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'C'.fake()->unique()->numberBetween(1, 999),
            'name' => ucfirst(fake()->words(2, true)),
            'type' => fake()->randomElement(CriteriaType::cases()),
            'weight' => fake()->randomFloat(2, 0.05, 0.50),
            'input_type' => fake()->randomElement(CriteriaInputType::cases()),
        ];
    }

    /**
     * Indicate that the criteria is a benefit type.
     */
    public function benefit(): static
    {
        return $this->state(fn () => [
            'type' => CriteriaType::Benefit,
        ]);
    }

    /**
     * Indicate that the criteria is a cost type.
     */
    public function cost(): static
    {
        return $this->state(fn () => [
            'type' => CriteriaType::Cost,
        ]);
    }

    /**
     * Indicate that the criteria has categorical input type.
     */
    public function categorical(): static
    {
        return $this->state(fn () => [
            'input_type' => CriteriaInputType::Categorical,
        ]);
    }

    /**
     * Indicate that the criteria has range input type.
     */
    public function range(): static
    {
        return $this->state(fn () => [
            'input_type' => CriteriaInputType::Range,
        ]);
    }
}
