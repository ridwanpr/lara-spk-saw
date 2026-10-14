<?php

namespace Database\Factories;

use App\Enums\TicketStatus;
use App\Models\Asset;
use App\Models\RepairTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RepairTicket>
 */
class RepairTicketFactory extends Factory
{
    protected $model = RepairTicket::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_number' => 'TCK-'.date('Ymd').'-'.fake()->unique()->numerify('####'),
            'asset_id' => Asset::factory(),
            'reporter_id' => User::factory()->staff(),
            'damage_description' => fake()->sentence(),
            'damage_photo' => null,
            'status' => TicketStatus::PendingVerification,
            'verifier_id' => null,
            'verified_at' => null,
            'estimated_cost' => null,
            'actual_cost' => null,
            'completion_notes' => null,
        ];
    }

    /**
     * Indicate that the ticket status is pending verification.
     */
    public function pendingVerification(): static
    {
        return $this->state(fn () => [
            'status' => TicketStatus::PendingVerification,
            'verifier_id' => null,
            'verified_at' => null,
        ]);
    }

    /**
     * Indicate that the ticket status is verified.
     */
    public function verified(): static
    {
        return $this->state(fn () => [
            'status' => TicketStatus::Verified,
            'verifier_id' => User::factory()->inspector(),
            'verified_at' => now(),
            'estimated_cost' => fake()->randomFloat(2, 50000, 1000000),
        ]);
    }

    /**
     * Indicate that the ticket status is under repair.
     */
    public function underRepair(): static
    {
        return $this->state(fn () => [
            'status' => TicketStatus::UnderRepair,
            'verifier_id' => User::factory()->inspector(),
            'verified_at' => now()->subDay(),
            'estimated_cost' => fake()->randomFloat(2, 50000, 1000000),
        ]);
    }

    /**
     * Indicate that the ticket status is completed.
     */
    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => TicketStatus::Completed,
            'verifier_id' => User::factory()->inspector(),
            'verified_at' => now()->subDays(2),
            'estimated_cost' => 500000,
            'actual_cost' => 450000,
            'completion_notes' => fake()->sentence(),
        ]);
    }

    /**
     * Indicate that the ticket status is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => TicketStatus::Rejected,
            'verifier_id' => User::factory()->inspector(),
            'verified_at' => now(),
            'completion_notes' => fake()->sentence(),
        ]);
    }
}
