<?php

use App\Models\Criteria;
use App\Models\SubCriteria;
use App\Models\User;
use App\Policies\SubCriteriaPolicy;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
});

describe('security and role protection for SubCriteria endpoints', function (): void {
    test('unauthenticated guests are redirected to login', function (): void {
        $criteria = Criteria::factory()->create();
        $subCriteria = SubCriteria::create([
            'criteria_id' => $criteria->id,
            'description' => 'Sangat Baik',
            'crisp_value' => 5,
        ]);

        $this->post("/admin/criterias/{$criteria->id}/sub-criterias", [
            'description' => 'Sangat Baik',
            'crisp_value' => 5,
        ])->assertRedirect('/login');

        $this->put("/admin/criterias/sub-criterias/{$subCriteria->id}", [
            'edit_description' => 'Sangat Baik',
            'edit_crisp_value' => 5,
        ])->assertRedirect('/login');

        $this->delete("/admin/criterias/sub-criterias/{$subCriteria->id}")
            ->assertRedirect('/login');
    });

    test('staff users are forbidden from all sub criteria endpoints', function (): void {
        $staff = User::factory()->staff()->create();
        $criteria = Criteria::factory()->create();
        $subCriteria = SubCriteria::create([
            'criteria_id' => $criteria->id,
            'description' => 'Sangat Baik',
            'crisp_value' => 5,
        ]);

        $this->actingAs($staff)->post("/admin/criterias/{$criteria->id}/sub-criterias", [
            'description' => 'Sangat Baik',
            'crisp_value' => 5,
        ])->assertForbidden();

        $this->actingAs($staff)->put("/admin/criterias/sub-criterias/{$subCriteria->id}", [
            'edit_description' => 'Sangat Baik',
            'edit_crisp_value' => 5,
        ])->assertForbidden();

        $this->actingAs($staff)->delete("/admin/criterias/sub-criterias/{$subCriteria->id}")
            ->assertForbidden();
    });

    test('inspector users are forbidden from all sub criteria endpoints', function (): void {
        $inspector = User::factory()->inspector()->create();
        $criteria = Criteria::factory()->create();
        $subCriteria = SubCriteria::create([
            'criteria_id' => $criteria->id,
            'description' => 'Sangat Baik',
            'crisp_value' => 5,
        ]);

        $this->actingAs($inspector)->post("/admin/criterias/{$criteria->id}/sub-criterias", [
            'description' => 'Sangat Baik',
            'crisp_value' => 5,
        ])->assertForbidden();

        $this->actingAs($inspector)->put("/admin/criterias/sub-criterias/{$subCriteria->id}", [
            'edit_description' => 'Sangat Baik',
            'edit_crisp_value' => 5,
        ])->assertForbidden();

        $this->actingAs($inspector)->delete("/admin/criterias/sub-criterias/{$subCriteria->id}")
            ->assertForbidden();
    });
});

describe('sub criteria policy permissions', function (): void {
    test('admin is authorized for all sub criteria policy abilities', function (): void {
        $admin = User::factory()->admin()->create();
        $criteria = Criteria::factory()->create();
        $subCriteria = SubCriteria::create([
            'criteria_id' => $criteria->id,
            'description' => 'Sangat Baik',
            'crisp_value' => 5,
        ]);
        $policy = new SubCriteriaPolicy;

        expect($policy->create($admin))->toBeTrue()
            ->and($policy->update($admin, $subCriteria))->toBeTrue()
            ->and($policy->delete($admin, $subCriteria))->toBeTrue();
    });

    test('staff is unauthorized for all sub criteria policy abilities', function (): void {
        $staff = User::factory()->staff()->create();
        $criteria = Criteria::factory()->create();
        $subCriteria = SubCriteria::create([
            'criteria_id' => $criteria->id,
            'description' => 'Sangat Baik',
            'crisp_value' => 5,
        ]);
        $policy = new SubCriteriaPolicy;

        expect($policy->create($staff))->toBeFalse()
            ->and($policy->update($staff, $subCriteria))->toBeFalse()
            ->and($policy->delete($staff, $subCriteria))->toBeFalse();
    });

    test('inspector is unauthorized for all sub criteria policy abilities', function (): void {
        $inspector = User::factory()->inspector()->create();
        $criteria = Criteria::factory()->create();
        $subCriteria = SubCriteria::create([
            'criteria_id' => $criteria->id,
            'description' => 'Sangat Baik',
            'crisp_value' => 5,
        ]);
        $policy = new SubCriteriaPolicy;

        expect($policy->create($inspector))->toBeFalse()
            ->and($policy->update($inspector, $subCriteria))->toBeFalse()
            ->and($policy->delete($inspector, $subCriteria))->toBeFalse();
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

    test('validation fails when crisp_value is already used for the same criteria on store', function (): void {
        $admin = User::factory()->admin()->create();
        $criteria = Criteria::factory()->create();
        SubCriteria::create([
            'criteria_id' => $criteria->id,
            'description' => 'Cukup',
            'crisp_value' => 3,
        ]);

        $response = $this->actingAs($admin)
            ->post("/admin/criterias/{$criteria->id}/sub-criterias", [
                'description' => 'Baik',
                'crisp_value' => 3,
            ]);

        $response->assertSessionHasErrors(['crisp_value']);
        $this->assertDatabaseCount('sub_criterias', 1);
    });

    test('admin can store sub criteria with the same crisp_value for a different criteria', function (): void {
        $admin = User::factory()->admin()->create();
        $criteriaA = Criteria::factory()->create();
        $criteriaB = Criteria::factory()->create();
        SubCriteria::create([
            'criteria_id' => $criteriaA->id,
            'description' => 'Cukup Kriteria A',
            'crisp_value' => 3,
        ]);

        $response = $this->actingAs($admin)
            ->from("/admin/criterias/{$criteriaB->id}")
            ->post("/admin/criterias/{$criteriaB->id}/sub-criterias", [
                'description' => 'Cukup Kriteria B',
                'crisp_value' => 3,
            ]);

        $response->assertRedirect("/admin/criterias/{$criteriaB->id}");
        $response->assertSessionHas('success', 'Data sub kriteria berhasil ditambahkan');
        $this->assertDatabaseCount('sub_criterias', 2);
        $this->assertDatabaseHas('sub_criterias', [
            'criteria_id' => $criteriaB->id,
            'description' => 'Cukup Kriteria B',
            'crisp_value' => 3,
        ]);
    });
});

describe('update sub criteria', function (): void {
    test('admin can update sub criteria description and crisp value', function (): void {
        $admin = User::factory()->admin()->create();
        $criteria = Criteria::factory()->categorical()->create();
        $subCriteria = SubCriteria::create([
            'criteria_id' => $criteria->id,
            'description' => 'Cukup',
            'crisp_value' => 3,
        ]);

        $response = $this->actingAs($admin)
            ->from("/admin/criterias/{$criteria->id}")
            ->put("/admin/criterias/sub-criterias/{$subCriteria->id}", [
                'edit_description' => 'Sangat Baik Sekali',
                'edit_crisp_value' => 5,
            ]);

        $response->assertRedirect("/admin/criterias/{$criteria->id}");
        $response->assertSessionHas('success', 'Data sub kriteria berhasil diubah');

        $subCriteria->refresh();
        expect($subCriteria->description)->toBe('Sangat Baik Sekali')
            ->and($subCriteria->crisp_value)->toBe(5)
            ->and($subCriteria->min_value)->toBeNull()
            ->and($subCriteria->max_value)->toBeNull();
    });

    test('admin can update sub criteria with range values', function (): void {
        $admin = User::factory()->admin()->create();
        $criteria = Criteria::factory()->range()->create();
        $subCriteria = SubCriteria::create([
            'criteria_id' => $criteria->id,
            'description' => 'Biaya Awal',
            'crisp_value' => 2,
            'min_value' => 100000.00,
            'max_value' => 200000.00,
        ]);

        $response = $this->actingAs($admin)
            ->from("/admin/criterias/{$criteria->id}")
            ->put("/admin/criterias/sub-criterias/{$subCriteria->id}", [
                'edit_description' => 'Biaya Direvisi',
                'edit_crisp_value' => 3,
                'edit_min_value' => 200001.00,
                'edit_max_value' => 500000.00,
            ]);

        $response->assertRedirect("/admin/criterias/{$criteria->id}");
        $response->assertSessionHas('success', 'Data sub kriteria berhasil diubah');

        $subCriteria->refresh();
        expect($subCriteria->description)->toBe('Biaya Direvisi')
            ->and($subCriteria->crisp_value)->toBe(3)
            ->and((float) $subCriteria->min_value)->toBe(200001.00)
            ->and((float) $subCriteria->max_value)->toBe(500000.00);
    });

    test('validation fails when required fields are missing on update', function (): void {
        $admin = User::factory()->admin()->create();
        $criteria = Criteria::factory()->create();
        $subCriteria = SubCriteria::create([
            'criteria_id' => $criteria->id,
            'description' => 'Deskripsi Tetap',
            'crisp_value' => 3,
        ]);

        $response = $this->actingAs($admin)
            ->put("/admin/criterias/sub-criterias/{$subCriteria->id}", []);

        $response->assertSessionHasErrors(['edit_description', 'edit_crisp_value']);

        $subCriteria->refresh();
        expect($subCriteria->description)->toBe('Deskripsi Tetap')
            ->and($subCriteria->crisp_value)->toBe(3);
    });

    test('validation fails when edit_description exceeds 255 characters', function (): void {
        $admin = User::factory()->admin()->create();
        $criteria = Criteria::factory()->create();
        $subCriteria = SubCriteria::create([
            'criteria_id' => $criteria->id,
            'description' => 'Deskripsi Tetap',
            'crisp_value' => 3,
        ]);

        $response = $this->actingAs($admin)
            ->put("/admin/criterias/sub-criterias/{$subCriteria->id}", [
                'edit_description' => str_repeat('a', 256),
                'edit_crisp_value' => 3,
            ]);

        $response->assertSessionHasErrors(['edit_description']);
    });

    test('validation fails for invalid edit_crisp_value values', function (mixed $invalidCrisp): void {
        $admin = User::factory()->admin()->create();
        $criteria = Criteria::factory()->create();
        $subCriteria = SubCriteria::create([
            'criteria_id' => $criteria->id,
            'description' => 'Deskripsi Tetap',
            'crisp_value' => 3,
        ]);

        $response = $this->actingAs($admin)
            ->put("/admin/criterias/sub-criterias/{$subCriteria->id}", [
                'edit_description' => 'Deskripsi Baru',
                'edit_crisp_value' => $invalidCrisp,
            ]);

        $response->assertSessionHasErrors(['edit_crisp_value']);
    })->with([
        'zero' => [0],
        'negative' => [-1],
        'greater than five' => [6],
        'non integer float' => [2.5],
        'non numeric string' => ['bukan-angka'],
    ]);

    test('validation fails when edit_min_value or edit_max_value is non-numeric', function (array $payload, string $errorField): void {
        $admin = User::factory()->admin()->create();
        $criteria = Criteria::factory()->create();
        $subCriteria = SubCriteria::create([
            'criteria_id' => $criteria->id,
            'description' => 'Deskripsi Tetap',
            'crisp_value' => 3,
        ]);

        $response = $this->actingAs($admin)
            ->put("/admin/criterias/sub-criterias/{$subCriteria->id}", array_merge([
                'edit_description' => 'Deskripsi Update',
                'edit_crisp_value' => 3,
            ], $payload));

        $response->assertSessionHasErrors([$errorField]);
    })->with([
        'invalid edit_min_value' => [['edit_min_value' => 'bukan-angka'], 'edit_min_value'],
        'invalid edit_max_value' => [['edit_max_value' => 'bukan-angka'], 'edit_max_value'],
    ]);

    test('returns 404 when updating non-existent sub criteria', function (): void {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->put('/admin/criterias/sub-criterias/999999', [
                'edit_description' => 'Update Non Existent',
                'edit_crisp_value' => 3,
            ]);

        $response->assertNotFound();
    });

    test('validation fails when edit_crisp_value is already used by another sub criteria of the same criteria on update', function (): void {
        $admin = User::factory()->admin()->create();
        $criteria = Criteria::factory()->create();
        SubCriteria::create([
            'criteria_id' => $criteria->id,
            'description' => 'Sangat Baik',
            'crisp_value' => 5,
        ]);
        $subCriteriaToUpdate = SubCriteria::create([
            'criteria_id' => $criteria->id,
            'description' => 'Baik',
            'crisp_value' => 4,
        ]);

        $response = $this->actingAs($admin)
            ->put("/admin/criterias/sub-criterias/{$subCriteriaToUpdate->id}", [
                'edit_description' => 'Baik Diubah',
                'edit_crisp_value' => 5,
            ]);

        $response->assertSessionHasErrors(['edit_crisp_value']);
        $subCriteriaToUpdate->refresh();
        expect($subCriteriaToUpdate->crisp_value)->toBe(4)
            ->and($subCriteriaToUpdate->description)->toBe('Baik');
    });

    test('admin can update sub criteria keeping its own crisp_value', function (): void {
        $admin = User::factory()->admin()->create();
        $criteria = Criteria::factory()->create();
        $subCriteria = SubCriteria::create([
            'criteria_id' => $criteria->id,
            'description' => 'Baik',
            'crisp_value' => 4,
        ]);

        $response = $this->actingAs($admin)
            ->from("/admin/criterias/{$criteria->id}")
            ->put("/admin/criterias/sub-criterias/{$subCriteria->id}", [
                'edit_description' => 'Baik Sekali',
                'edit_crisp_value' => 4,
            ]);

        $response->assertRedirect("/admin/criterias/{$criteria->id}");
        $response->assertSessionHas('success', 'Data sub kriteria berhasil diubah');

        $subCriteria->refresh();
        expect($subCriteria->description)->toBe('Baik Sekali')
            ->and($subCriteria->crisp_value)->toBe(4);
    });

    test('admin can update sub criteria to use crisp_value that exists in another criteria', function (): void {
        $admin = User::factory()->admin()->create();
        $criteriaA = Criteria::factory()->create();
        $criteriaB = Criteria::factory()->create();
        SubCriteria::create([
            'criteria_id' => $criteriaA->id,
            'description' => 'Sangat Baik A',
            'crisp_value' => 5,
        ]);
        $subCriteriaB = SubCriteria::create([
            'criteria_id' => $criteriaB->id,
            'description' => 'Baik B',
            'crisp_value' => 4,
        ]);

        $response = $this->actingAs($admin)
            ->from("/admin/criterias/{$criteriaB->id}")
            ->put("/admin/criterias/sub-criterias/{$subCriteriaB->id}", [
                'edit_description' => 'Sangat Baik B',
                'edit_crisp_value' => 5,
            ]);

        $response->assertRedirect("/admin/criterias/{$criteriaB->id}");
        $response->assertSessionHas('success', 'Data sub kriteria berhasil diubah');

        $subCriteriaB->refresh();
        expect($subCriteriaB->description)->toBe('Sangat Baik B')
            ->and($subCriteriaB->crisp_value)->toBe(5);
    });
});

describe('destroy sub criteria', function (): void {
    test('admin can delete an existing sub criteria', function (): void {
        $admin = User::factory()->admin()->create();
        $criteria = Criteria::factory()->create();
        $subCriteria = SubCriteria::create([
            'criteria_id' => $criteria->id,
            'description' => 'Cukup',
            'crisp_value' => 3,
        ]);

        $response = $this->actingAs($admin)
            ->from("/admin/criterias/{$criteria->id}")
            ->delete("/admin/criterias/sub-criterias/{$subCriteria->id}");

        $response->assertRedirect("/admin/criterias/{$criteria->id}");
        $response->assertSessionHas('success', 'Data sub kriteria berhasil dihapus');
        $this->assertModelMissing($subCriteria);
        expect($criteria->subCriterias()->count())->toBe(0);
    });

    test('deleting a sub criteria does not delete sibling sub criterias or parent criteria', function (): void {
        $admin = User::factory()->admin()->create();
        $criteria = Criteria::factory()->create();
        $subCriteria1 = SubCriteria::create([
            'criteria_id' => $criteria->id,
            'description' => 'Sangat Baik',
            'crisp_value' => 5,
        ]);
        $subCriteria2 = SubCriteria::create([
            'criteria_id' => $criteria->id,
            'description' => 'Baik',
            'crisp_value' => 4,
        ]);

        $response = $this->actingAs($admin)
            ->from("/admin/criterias/{$criteria->id}")
            ->delete("/admin/criterias/sub-criterias/{$subCriteria1->id}");

        $response->assertRedirect("/admin/criterias/{$criteria->id}");
        $this->assertModelMissing($subCriteria1);
        $this->assertModelExists($subCriteria2);
        $this->assertModelExists($criteria);
        expect($criteria->subCriterias()->count())->toBe(1);
    });

    test('returns 404 when deleting non-existent sub criteria', function (): void {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->delete('/admin/criterias/sub-criterias/999999');

        $response->assertNotFound();
    });
});
