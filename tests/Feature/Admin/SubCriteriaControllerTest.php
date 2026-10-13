<?php

use App\Models\Criteria;
use App\Models\User;
use App\Policies\SubCriteriaPolicy;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
});

describe('security and role protection for SubCriteria store endpoint', function (): void {
    test('unauthenticated guests are redirected to login', function (): void {
        $criteria = Criteria::factory()->create();

        $response = $this->post("/admin/criterias/{$criteria->id}/sub-criterias", [
            'description' => 'Sangat Baik',
            'crisp_value' => 5,
        ]);

        $response->assertRedirect('/login');
    });

    test('staff users are forbidden from storing sub criteria', function (): void {
        $staff = User::factory()->staff()->create();
        $criteria = Criteria::factory()->create();

        $response = $this->actingAs($staff)->post("/admin/criterias/{$criteria->id}/sub-criterias", [
            'description' => 'Sangat Baik',
            'crisp_value' => 5,
        ]);

        $response->assertForbidden();
    });

    test('inspector users are forbidden from storing sub criteria', function (): void {
        $inspector = User::factory()->inspector()->create();
        $criteria = Criteria::factory()->create();

        $response = $this->actingAs($inspector)->post("/admin/criterias/{$criteria->id}/sub-criterias", [
            'description' => 'Sangat Baik',
            'crisp_value' => 5,
        ]);

        $response->assertForbidden();
    });
});

describe('sub criteria policy permissions', function (): void {
    test('admin is authorized to create sub criteria', function (): void {
        $admin = User::factory()->admin()->create();
        $policy = new SubCriteriaPolicy;

        expect($policy->create($admin))->toBeTrue();
    });

    test('staff is unauthorized to create sub criteria', function (): void {
        $staff = User::factory()->staff()->create();
        $policy = new SubCriteriaPolicy;

        expect($policy->create($staff))->toBeFalse();
    });

    test('inspector is unauthorized to create sub criteria', function (): void {
        $inspector = User::factory()->inspector()->create();
        $policy = new SubCriteriaPolicy;

        expect($policy->create($inspector))->toBeFalse();
    });
});

describe('store sub criteria', function (): void {
    test('admin can store sub criteria for categorical criteria', function (): void {
        $admin = User::factory()->admin()->create();
        $criteria = Criteria::factory()->categorical()->create();

        $response = $this->actingAs($admin)
            ->from("/admin/criterias/{$criteria->id}")
            ->post("/admin/criterias/{$criteria->id}/sub-criterias", [
                'description' => 'Sangat Baik',
                'crisp_value' => 5,
            ]);

        $response->assertRedirect("/admin/criterias/{$criteria->id}");
        $response->assertSessionHas('success', 'Data sub kriteria berhasil ditambahkan');

        $this->assertDatabaseHas('sub_criterias', [
            'criteria_id' => $criteria->id,
            'description' => 'Sangat Baik',
            'crisp_value' => 5,
            'min_value' => null,
            'max_value' => null,
        ]);

        expect($criteria->subCriterias)->toHaveCount(1)
            ->and($criteria->subCriterias->first()->description)->toBe('Sangat Baik')
            ->and($criteria->subCriterias->first()->crisp_value)->toBe(5);
    });

    test('admin can store sub criteria with range values', function (): void {
        $admin = User::factory()->admin()->create();
        $criteria = Criteria::factory()->range()->create();

        $response = $this->actingAs($admin)
            ->from("/admin/criterias/{$criteria->id}")
            ->post("/admin/criterias/{$criteria->id}/sub-criterias", [
                'description' => 'Rentang Biaya Ringan',
                'crisp_value' => 1,
                'min_value' => 100000.00,
                'max_value' => 500000.00,
            ]);

        $response->assertRedirect("/admin/criterias/{$criteria->id}");
        $response->assertSessionHas('success', 'Data sub kriteria berhasil ditambahkan');

        $this->assertDatabaseHas('sub_criterias', [
            'criteria_id' => $criteria->id,
            'description' => 'Rentang Biaya Ringan',
            'crisp_value' => 1,
            'min_value' => 100000.00,
            'max_value' => 500000.00,
        ]);

        $subCriteria = $criteria->subCriterias()->first();
        expect($subCriteria)->not->toBeNull()
            ->and((float) $subCriteria->min_value)->toBe(100000.00)
            ->and((float) $subCriteria->max_value)->toBe(500000.00);
    });

    test('validation fails when required fields are missing', function (): void {
        $admin = User::factory()->admin()->create();
        $criteria = Criteria::factory()->create();

        $response = $this->actingAs($admin)
            ->post("/admin/criterias/{$criteria->id}/sub-criterias", []);

        $response->assertSessionHasErrors(['description', 'crisp_value']);
        $this->assertDatabaseCount('sub_criterias', 0);
    });

    test('validation fails when description exceeds 255 characters', function (): void {
        $admin = User::factory()->admin()->create();
        $criteria = Criteria::factory()->create();

        $response = $this->actingAs($admin)
            ->post("/admin/criterias/{$criteria->id}/sub-criterias", [
                'description' => str_repeat('a', 256),
                'crisp_value' => 3,
            ]);

        $response->assertSessionHasErrors(['description']);
        $this->assertDatabaseCount('sub_criterias', 0);
    });

    test('validation fails for invalid crisp values', function (mixed $invalidCrisp): void {
        $admin = User::factory()->admin()->create();
        $criteria = Criteria::factory()->create();

        $response = $this->actingAs($admin)
            ->post("/admin/criterias/{$criteria->id}/sub-criterias", [
                'description' => 'Kategori Sedang',
                'crisp_value' => $invalidCrisp,
            ]);

        $response->assertSessionHasErrors(['crisp_value']);
        $this->assertDatabaseCount('sub_criterias', 0);
    })->with([
        'zero' => [0],
        'negative' => [-1],
        'greater than five' => [6],
        'non integer float' => [2.5],
        'non numeric string' => ['bukan-angka'],
    ]);

    test('validation fails when min_value or max_value is non-numeric', function (array $payload, string $errorField): void {
        $admin = User::factory()->admin()->create();
        $criteria = Criteria::factory()->create();

        $response = $this->actingAs($admin)
            ->post("/admin/criterias/{$criteria->id}/sub-criterias", array_merge([
                'description' => 'Rentang Tidak Valid',
                'crisp_value' => 3,
            ], $payload));

        $response->assertSessionHasErrors([$errorField]);
        $this->assertDatabaseCount('sub_criterias', 0);
    })->with([
        'invalid min_value' => [['min_value' => 'bukan-angka'], 'min_value'],
        'invalid max_value' => [['max_value' => 'bukan-angka'], 'max_value'],
    ]);

    test('returns 404 when storing sub criteria for non-existent criteria', function (): void {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->post('/admin/criterias/999999/sub-criterias', [
                'description' => 'Sub Kriteria Orphan',
                'crisp_value' => 3,
            ]);

        $response->assertNotFound();
        $this->assertDatabaseCount('sub_criterias', 0);
    });
});
