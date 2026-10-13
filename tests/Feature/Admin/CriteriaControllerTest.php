<?php

use App\Enums\CriteriaInputType;
use App\Enums\CriteriaType;
use App\Models\Criteria;
use App\Models\SubCriteria;
use App\Models\User;
use App\Policies\CriteriaPolicy;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
});

describe('security and role protection for Criteria endpoints', function (): void {
    test('unauthenticated guests are redirected to login', function (): void {
        $criteria = Criteria::factory()->create();

        $this->get('/admin/criterias')->assertRedirect('/login');
        $this->get('/admin/criterias/create')->assertRedirect('/login');
        $this->post('/admin/criterias', [])->assertRedirect('/login');
        $this->get("/admin/criterias/{$criteria->id}")->assertRedirect('/login');
        $this->put("/admin/criterias/{$criteria->id}", [])->assertRedirect('/login');
        $this->delete("/admin/criterias/{$criteria->id}")->assertRedirect('/login');
    });

    test('staff users are forbidden from all criteria endpoints', function (): void {
        $staff = User::factory()->staff()->create();
        $criteria = Criteria::factory()->create();

        $this->actingAs($staff)->get('/admin/criterias')->assertForbidden();
        $this->actingAs($staff)->get('/admin/criterias/create')->assertForbidden();
        $this->actingAs($staff)->post('/admin/criterias', [
            'name' => 'Kriteria Uji',
            'code' => 'C-TEST',
            'type' => CriteriaType::Benefit->value,
            'weight' => 0.25,
            'input_type' => CriteriaInputType::Categorical->value,
        ])->assertForbidden();
        $this->actingAs($staff)->get("/admin/criterias/{$criteria->id}")->assertForbidden();
        $this->actingAs($staff)->put("/admin/criterias/{$criteria->id}", [
            'name' => 'Kriteria Uji',
            'code' => 'C-TEST',
            'type' => CriteriaType::Benefit->value,
            'weight' => 0.25,
            'input_type' => CriteriaInputType::Categorical->value,
        ])->assertForbidden();
        $this->actingAs($staff)->delete("/admin/criterias/{$criteria->id}")->assertForbidden();
    });

    test('inspector users are forbidden from all criteria endpoints', function (): void {
        $inspector = User::factory()->inspector()->create();
        $criteria = Criteria::factory()->create();

        $this->actingAs($inspector)->get('/admin/criterias')->assertForbidden();
        $this->actingAs($inspector)->get('/admin/criterias/create')->assertForbidden();
        $this->actingAs($inspector)->post('/admin/criterias', [])->assertForbidden();
        $this->actingAs($inspector)->get("/admin/criterias/{$criteria->id}")->assertForbidden();
        $this->actingAs($inspector)->put("/admin/criterias/{$criteria->id}", [])->assertForbidden();
        $this->actingAs($inspector)->delete("/admin/criterias/{$criteria->id}")->assertForbidden();
    });
});

describe('criteria policy permissions', function (): void {
    test('admin is authorized for all policy abilities', function (): void {
        $admin = User::factory()->admin()->create();
        $criteria = Criteria::factory()->create();
        $policy = new CriteriaPolicy;

        expect($policy->viewAny($admin))->toBeTrue()
            ->and($policy->create($admin))->toBeTrue()
            ->and($policy->update($admin, $criteria))->toBeTrue()
            ->and($policy->delete($admin, $criteria))->toBeTrue();
    });

    test('staff is unauthorized for all policy abilities', function (): void {
        $staff = User::factory()->staff()->create();
        $criteria = Criteria::factory()->create();
        $policy = new CriteriaPolicy;

        expect($policy->viewAny($staff))->toBeFalse()
            ->and($policy->create($staff))->toBeFalse()
            ->and($policy->update($staff, $criteria))->toBeFalse()
            ->and($policy->delete($staff, $criteria))->toBeFalse();
    });

    test('inspector is unauthorized for all policy abilities', function (): void {
        $inspector = User::factory()->inspector()->create();
        $criteria = Criteria::factory()->create();
        $policy = new CriteriaPolicy;

        expect($policy->viewAny($inspector))->toBeFalse()
            ->and($policy->create($inspector))->toBeFalse()
            ->and($policy->update($inspector, $criteria))->toBeFalse()
            ->and($policy->delete($inspector, $criteria))->toBeFalse();
    });
});

describe('criteria list (index)', function (): void {
    test('admin can view criterias list and see criteria details', function (): void {
        $admin = User::factory()->admin()->create();
        $benefitCriteria = Criteria::factory()->benefit()->categorical()->create([
            'code' => 'C1',
            'name' => 'Kondisi Fisik',
            'weight' => 0.35,
        ]);
        $costCriteria = Criteria::factory()->cost()->range()->create([
            'code' => 'C2',
            'name' => 'Biaya Perbaikan',
            'weight' => 0.20,
        ]);

        $response = $this->actingAs($admin)->get('/admin/criterias');

        $response->assertOk();
        $response->assertViewIs('admin.criterias.index');
        $response->assertViewHas('criterias');
        $response->assertSee('Daftar Kriteria');
        $response->assertSee('C1');
        $response->assertSee('Kondisi Fisik');
        $response->assertSee('0.35');
        $response->assertSee('benefit');
        $response->assertSee('Kategorikal');
        $response->assertSee('C2');
        $response->assertSee('Biaya Perbaikan');
        $response->assertSee('0.20');
        $response->assertSee('cost');
        $response->assertSee('Rentang Nilai');
    });

    test('admin sees empty state message when no criteria exist', function (): void {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/criterias');

        $response->assertOk();
        $response->assertSee('Data kriteria tidak ditemukan.');
    });
});

describe('create criteria page', function (): void {
    test('admin can access create criteria page', function (): void {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/criterias/create');

        $response->assertOk();
        $response->assertViewIs('admin.criterias.create');
        $response->assertSee('Tambah Kriteria Baru');
        $response->assertSee('Formulir Tambah Kriteria');
    });
});

describe('store criteria', function (): void {
    test('admin can store a new benefit criteria with categorical input', function (): void {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/criterias', [
            'name' => 'Tingkat Kerusakan',
            'code' => 'C1',
            'type' => CriteriaType::Benefit->value,
            'weight' => 0.30,
            'input_type' => CriteriaInputType::Categorical->value,
        ]);

        $response->assertRedirect('/admin/criterias');
        $response->assertSessionHas('success', 'Data kriteria berhasil ditambahkan');

        $this->assertDatabaseHas('criterias', [
            'code' => 'C1',
            'name' => 'Tingkat Kerusakan',
            'type' => 'benefit',
            'weight' => 0.30,
            'input_type' => 'categorical',
        ]);
    });

    test('admin can store a new cost criteria with range input', function (): void {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/criterias', [
            'name' => 'Estimasi Biaya',
            'code' => 'C2',
            'type' => CriteriaType::Cost->value,
            'weight' => 0.25,
            'input_type' => CriteriaInputType::Range->value,
        ]);

        $response->assertRedirect('/admin/criterias');
        $response->assertSessionHas('success', 'Data kriteria berhasil ditambahkan');

        $this->assertDatabaseHas('criterias', [
            'code' => 'C2',
            'name' => 'Estimasi Biaya',
            'type' => 'cost',
            'weight' => 0.25,
            'input_type' => 'range',
        ]);
    });

    test('validation fails when required fields are missing', function (): void {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/criterias', []);

        $response->assertSessionHasErrors(['name', 'code', 'type', 'weight', 'input_type']);
        $this->assertDatabaseCount('criterias', 0);
    });

    test('validation fails when criteria code is duplicate', function (): void {
        $admin = User::factory()->admin()->create();
        Criteria::factory()->create(['code' => 'C-EXISTING']);

        $response = $this->actingAs($admin)->post('/admin/criterias', [
            'name' => 'Kriteria Baru',
            'code' => 'C-EXISTING',
            'type' => CriteriaType::Benefit->value,
            'weight' => 0.20,
            'input_type' => CriteriaInputType::Categorical->value,
        ]);

        $response->assertSessionHasErrors(['code']);
    });

    test('validation fails when type is invalid', function (): void {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/criterias', [
            'name' => 'Kriteria Invalid Type',
            'code' => 'C-INVALID-TYPE',
            'type' => 'not-a-type',
            'weight' => 0.20,
            'input_type' => CriteriaInputType::Categorical->value,
        ]);

        $response->assertSessionHasErrors(['type']);
    });

    test('validation fails when input type is invalid', function (): void {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/criterias', [
            'name' => 'Kriteria Invalid Input Type',
            'code' => 'C-INVALID-INPUT',
            'type' => CriteriaType::Benefit->value,
            'weight' => 0.20,
            'input_type' => 'not-an-input-type',
        ]);

        $response->assertSessionHasErrors(['input_type']);
    });

    test('validation fails for invalid weight values', function (mixed $invalidWeight): void {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/criterias', [
            'name' => 'Kriteria Invalid Weight',
            'code' => 'C-INVALID-WEIGHT',
            'type' => CriteriaType::Benefit->value,
            'weight' => $invalidWeight,
            'input_type' => CriteriaInputType::Categorical->value,
        ]);

        $response->assertSessionHasErrors(['weight']);
    })->with([
        'zero' => [0],
        'negative' => [-0.25],
        'greater than one' => [1.05],
        'non-numeric string' => ['bukan-angka'],
    ]);
});

describe('destroy criteria', function (): void {
    test('admin can delete an existing criteria', function (): void {
        $admin = User::factory()->admin()->create();
        $criteria = Criteria::factory()->create();

        $response = $this->actingAs($admin)->delete("/admin/criterias/{$criteria->id}");

        $response->assertRedirect('/admin/criterias');
        $response->assertSessionHas('success', 'Kriteria berhasil dihapus.');
        $this->assertModelMissing($criteria);
    });

    test('deleting criteria cascades and deletes its sub criterias', function (): void {
        $admin = User::factory()->admin()->create();
        $criteria = Criteria::factory()->create();
        $subCriteria = SubCriteria::create([
            'criteria_id' => $criteria->id,
            'description' => 'Rusak Ringan',
            'crisp_value' => 2,
        ]);

        $response = $this->actingAs($admin)->delete("/admin/criterias/{$criteria->id}");

        $response->assertRedirect('/admin/criterias');
        $this->assertModelMissing($criteria);
        $this->assertDatabaseMissing('sub_criterias', ['id' => $subCriteria->id]);
    });

    test('returns 404 when deleting a non-existent criteria', function (): void {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->delete('/admin/criterias/999999');

        $response->assertNotFound();
    });
});

describe('show criteria', function (): void {
    test('admin can access show criteria page and see its details', function (): void {
        $admin = User::factory()->admin()->create();
        $criteria = Criteria::factory()->benefit()->categorical()->create([
            'code' => 'C1',
            'name' => 'Kondisi Fisik',
            'weight' => 0.35,
        ]);

        $response = $this->actingAs($admin)->get("/admin/criterias/{$criteria->id}");

        $response->assertOk();
        $response->assertViewIs('admin.criterias.show');
        $response->assertViewHas('criteria', fn (Criteria $item): bool => $item->id === $criteria->id);
        $response->assertSee('Detail Kriteria');
        $response->assertSee('Formulir Edit Kriteria');
        $response->assertSee('Kondisi Fisik');
        $response->assertSee('C1');
        $response->assertSee('0.35');
        $response->assertSee('benefit');
        $response->assertSee('Kategorikal');
    });

    test('returns 404 when viewing non-existent criteria', function (): void {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/criterias/999999');

        $response->assertNotFound();
    });
});

describe('update criteria', function (): void {
    test('admin can update criteria details', function (): void {
        $admin = User::factory()->admin()->create();
        $criteria = Criteria::factory()->benefit()->categorical()->create([
            'code' => 'C1',
            'name' => 'Kondisi Fisik',
            'weight' => 0.35,
        ]);

        $response = $this->actingAs($admin)
            ->from("/admin/criterias/{$criteria->id}")
            ->put("/admin/criterias/{$criteria->id}", [
                'name' => 'Kondisi Bangunan',
                'code' => 'C1-BARU',
                'type' => CriteriaType::Cost->value,
                'weight' => 0.40,
                'input_type' => CriteriaInputType::Range->value,
            ]);

        $response->assertRedirect("/admin/criterias/{$criteria->id}");
        $response->assertSessionHas('success', 'Kriteria berhasil diupdate');

        $this->assertDatabaseHas('criterias', [
            'id' => $criteria->id,
            'name' => 'Kondisi Bangunan',
            'code' => 'C1-BARU',
            'type' => 'cost',
            'weight' => 0.40,
            'input_type' => 'range',
        ]);

        $criteria->refresh();
        expect($criteria->name)->toBe('Kondisi Bangunan')
            ->and($criteria->code)->toBe('C1-BARU')
            ->and($criteria->type)->toBe(CriteriaType::Cost)
            ->and((float) $criteria->weight)->toBe(0.40)
            ->and($criteria->input_type)->toBe(CriteriaInputType::Range);
    });

    test('admin can update criteria without changing unique code', function (): void {
        $admin = User::factory()->admin()->create();
        $criteria = Criteria::factory()->create([
            'code' => 'C-TETAP',
            'name' => 'Nama Lama',
        ]);

        $response = $this->actingAs($admin)
            ->from("/admin/criterias/{$criteria->id}")
            ->put("/admin/criterias/{$criteria->id}", [
                'name' => 'Nama Baru',
                'code' => 'C-TETAP',
                'type' => CriteriaType::Benefit->value,
                'weight' => 0.20,
                'input_type' => CriteriaInputType::Categorical->value,
            ]);

        $response->assertRedirect("/admin/criterias/{$criteria->id}");
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success', 'Kriteria berhasil diupdate');

        $this->assertDatabaseHas('criterias', [
            'id' => $criteria->id,
            'name' => 'Nama Baru',
            'code' => 'C-TETAP',
        ]);
    });

    test('validation fails when required fields are missing on update', function (): void {
        $admin = User::factory()->admin()->create();
        $criteria = Criteria::factory()->create([
            'name' => 'Kriteria Asli',
            'code' => 'C-ASLI',
        ]);

        $response = $this->actingAs($admin)->put("/admin/criterias/{$criteria->id}", []);

        $response->assertSessionHasErrors(['name', 'code', 'type', 'weight', 'input_type']);

        $criteria->refresh();
        expect($criteria->name)->toBe('Kriteria Asli')
            ->and($criteria->code)->toBe('C-ASLI');
    });

    test('validation fails when updating code with existing duplicate code', function (): void {
        $admin = User::factory()->admin()->create();
        Criteria::factory()->create(['code' => 'C-EXISTING']);
        $criteria = Criteria::factory()->create(['code' => 'C-MINE']);

        $response = $this->actingAs($admin)->put("/admin/criterias/{$criteria->id}", [
            'name' => 'Kriteria Edit',
            'code' => 'C-EXISTING',
            'type' => CriteriaType::Benefit->value,
            'weight' => 0.20,
            'input_type' => CriteriaInputType::Categorical->value,
        ]);

        $response->assertSessionHasErrors(['code']);

        $criteria->refresh();
        expect($criteria->code)->toBe('C-MINE');
    });

    test('validation fails when updating with invalid type', function (): void {
        $admin = User::factory()->admin()->create();
        $criteria = Criteria::factory()->create();

        $response = $this->actingAs($admin)->put("/admin/criterias/{$criteria->id}", [
            'name' => 'Kriteria Invalid Type',
            'code' => $criteria->code,
            'type' => 'invalid-type',
            'weight' => 0.20,
            'input_type' => CriteriaInputType::Categorical->value,
        ]);

        $response->assertSessionHasErrors(['type']);
    });

    test('validation fails when updating with invalid input type', function (): void {
        $admin = User::factory()->admin()->create();
        $criteria = Criteria::factory()->create();

        $response = $this->actingAs($admin)->put("/admin/criterias/{$criteria->id}", [
            'name' => 'Kriteria Invalid Input Type',
            'code' => $criteria->code,
            'type' => CriteriaType::Benefit->value,
            'weight' => 0.20,
            'input_type' => 'invalid-input-type',
        ]);

        $response->assertSessionHasErrors(['input_type']);
    });

    test('validation fails when updating with invalid weight values', function (mixed $invalidWeight): void {
        $admin = User::factory()->admin()->create();
        $criteria = Criteria::factory()->create();

        $response = $this->actingAs($admin)->put("/admin/criterias/{$criteria->id}", [
            'name' => 'Kriteria Invalid Weight',
            'code' => $criteria->code,
            'type' => CriteriaType::Benefit->value,
            'weight' => $invalidWeight,
            'input_type' => CriteriaInputType::Categorical->value,
        ]);

        $response->assertSessionHasErrors(['weight']);
    })->with([
        'zero' => [0],
        'negative' => [-0.25],
        'greater than one' => [1.05],
        'non-numeric string' => ['bukan-angka'],
    ]);

    test('returns 404 when updating non-existent criteria', function (): void {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->put('/admin/criterias/999999', [
            'name' => 'Tidak Ada',
            'code' => 'C-NONE',
            'type' => CriteriaType::Benefit->value,
            'weight' => 0.20,
            'input_type' => CriteriaInputType::Categorical->value,
        ]);

        $response->assertNotFound();
    });
});
