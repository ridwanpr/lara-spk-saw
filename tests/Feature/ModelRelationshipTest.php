<?php

use App\Enums\AssetCondition;
use App\Enums\CriteriaInputType;
use App\Enums\CriteriaType;
use App\Enums\RoleName;
use App\Enums\TicketStatus;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Criteria;
use App\Models\RepairTicket;
use App\Models\Role;
use App\Models\Room;
use App\Models\SubCriteria;
use App\Models\TicketEvaluation;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user model belongs to role and has helper methods', function () {
    $adminUser = User::factory()->admin()->create();
    $inspectorUser = User::factory()->inspector()->create();
    $staffUser = User::factory()->staff()->create();

    expect($adminUser->role)->toBeInstanceOf(Role::class)
        ->and($adminUser->isAdmin())->toBeTrue()
        ->and($adminUser->isInspector())->toBeFalse()
        ->and($adminUser->isStaff())->toBeFalse();

    expect($inspectorUser->isAdmin())->toBeFalse()
        ->and($inspectorUser->isInspector())->toBeTrue()
        ->and($inspectorUser->isStaff())->toBeFalse();

    expect($staffUser->isAdmin())->toBeFalse()
        ->and($staffUser->isInspector())->toBeFalse()
        ->and($staffUser->isStaff())->toBeTrue();
});

test('asset relationships and enums work correctly', function () {
    $category = AssetCategory::create([
        'name' => 'Elektronik',
        'description' => 'Peralatan elektronik sekolah',
    ]);

    $room = Room::create([
        'name' => 'Laboratorium Komputer',
        'code' => 'LAB-KOMP-01',
    ]);

    $asset = Asset::create([
        'code' => 'AST-PC-001',
        'name' => 'PC Client 01',
        'category_id' => $category->id,
        'room_id' => $room->id,
        'procurement_year' => 2024,
        'condition' => AssetCondition::Damaged,
    ]);

    expect($asset->category->id)->toBe($category->id)
        ->and($asset->room->id)->toBe($room->id)
        ->and($asset->condition)->toBe(AssetCondition::Damaged)
        ->and($room->assets->first()->id)->toBe($asset->id)
        ->and($category->assets->first()->id)->toBe($asset->id);
});

test('repair ticket and evaluations work with relationships and unique integrity', function () {
    $category = AssetCategory::create(['name' => 'Furniture', 'description' => 'Meja Kursi']);
    $room = Room::create(['name' => 'Kelas 7A', 'code' => 'KLS-7A']);
    $asset = Asset::create([
        'code' => 'MEJA-001',
        'name' => 'Meja Guru',
        'category_id' => $category->id,
        'room_id' => $room->id,
        'procurement_year' => 2023,
        'condition' => AssetCondition::Damaged,
    ]);

    $reporter = User::factory()->staff()->create();
    $verifier = User::factory()->inspector()->create();

    $ticket = RepairTicket::create([
        'ticket_number' => 'TCK-2026-0001',
        'asset_id' => $asset->id,
        'reporter_id' => $reporter->id,
        'damage_description' => 'Kaki meja patah sebelah kanan',
        'status' => TicketStatus::Verified,
        'verifier_id' => $verifier->id,
        'verified_at' => now(),
        'estimated_cost' => 150000.00,
    ]);

    expect($ticket->asset->id)->toBe($asset->id)
        ->and($ticket->reporter->id)->toBe($reporter->id)
        ->and($ticket->verifier->id)->toBe($verifier->id)
        ->and($ticket->status)->toBe(TicketStatus::Verified);

    $criteria = Criteria::create([
        'code' => 'C1',
        'name' => 'Tingkat Kerusakan',
        'type' => CriteriaType::Benefit,
        'weight' => 0.35,
        'input_type' => CriteriaInputType::Categorical,
    ]);

    $subCriteria = SubCriteria::create([
        'criteria_id' => $criteria->id,
        'description' => 'Rusak Berat',
        'crisp_value' => 5,
    ]);

    $evaluation = TicketEvaluation::create([
        'ticket_id' => $ticket->id,
        'criteria_id' => $criteria->id,
        'sub_criteria_id' => $subCriteria->id,
        'score' => 5,
    ]);

    expect($ticket->evaluations)->toHaveCount(1)
        ->and($evaluation->criteria->id)->toBe($criteria->id)
        ->and($evaluation->subCriteria->id)->toBe($subCriteria->id);

    // Duplicate evaluation for same ticket and criteria should fail due to unique constraint
    expect(function () use ($ticket, $criteria, $subCriteria) {
        TicketEvaluation::create([
            'ticket_id' => $ticket->id,
            'criteria_id' => $criteria->id,
            'sub_criteria_id' => $subCriteria->id,
            'score' => 4,
        ]);
    })->toThrow(QueryException::class);
});

test('roles seeder populates system roles', function () {
    $this->seed(RoleSeeder::class);

    expect(Role::where('name', RoleName::Admin->value)->exists())->toBeTrue()
        ->and(Role::where('name', RoleName::Inspector->value)->exists())->toBeTrue()
        ->and(Role::where('name', RoleName::Staff->value)->exists())->toBeTrue();
});

test('user seeders create users for each role and database seeder executes cleanly', function () {
    $this->seed(DatabaseSeeder::class);

    $admin = User::where('email', 'admin@example.com')->first();
    $inspector = User::where('email', 'inspector@example.com')->first();
    $staff = User::where('email', 'staff@example.com')->first();

    expect($admin)->not->toBeNull()
        ->and($admin->isAdmin())->toBeTrue()
        ->and($inspector)->not->toBeNull()
        ->and($inspector->isInspector())->toBeTrue()
        ->and($staff)->not->toBeNull()
        ->and($staff->isStaff())->toBeTrue();
});
