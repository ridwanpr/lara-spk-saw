<?php

use App\Enums\AssetCondition;
use App\Enums\TicketStatus;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Criteria;
use App\Models\RepairTicket;
use App\Models\Room;
use App\Models\SubCriteria;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
});

describe('security and role protection for Master Aset', function (): void {
    test('unauthenticated guests are redirected to login', function (): void {
        $room = Room::factory()->create();
        $category = AssetCategory::factory()->create();
        $asset = Asset::factory()->create();

        // Rooms
        $this->get('/admin/rooms')->assertRedirect('/login');
        $this->get('/admin/rooms/create')->assertRedirect('/login');
        $this->post('/admin/rooms', [])->assertRedirect('/login');
        $this->get("/admin/rooms/{$room->id}/edit")->assertRedirect('/login');
        $this->put("/admin/rooms/{$room->id}", [])->assertRedirect('/login');
        $this->delete("/admin/rooms/{$room->id}")->assertRedirect('/login');

        // Asset Categories
        $this->get('/admin/asset-categories')->assertRedirect('/login');
        $this->get('/admin/asset-categories/create')->assertRedirect('/login');
        $this->post('/admin/asset-categories', [])->assertRedirect('/login');
        $this->get("/admin/asset-categories/{$category->id}/edit")->assertRedirect('/login');
        $this->put("/admin/asset-categories/{$category->id}", [])->assertRedirect('/login');
        $this->delete("/admin/asset-categories/{$category->id}")->assertRedirect('/login');

        // Assets
        $this->get('/admin/assets')->assertRedirect('/login');
        $this->get('/admin/assets/create')->assertRedirect('/login');
        $this->post('/admin/assets', [])->assertRedirect('/login');
        $this->get("/admin/assets/{$asset->id}/edit")->assertRedirect('/login');
        $this->put("/admin/assets/{$asset->id}", [])->assertRedirect('/login');
        $this->delete("/admin/assets/{$asset->id}")->assertRedirect('/login');
    });

    test('staff users are forbidden from all Master Aset endpoints', function (): void {
        $staff = User::factory()->staff()->create();
        $room = Room::factory()->create();
        $category = AssetCategory::factory()->create();
        $asset = Asset::factory()->create();

        $this->actingAs($staff)->get('/admin/rooms')->assertStatus(403);
        $this->actingAs($staff)->get('/admin/asset-categories')->assertStatus(403);
        $this->actingAs($staff)->get('/admin/assets')->assertStatus(403);

        $this->actingAs($staff)->post('/admin/rooms', ['name' => 'Test', 'code' => 'TEST'])->assertStatus(403);
        $this->actingAs($staff)->delete("/admin/rooms/{$room->id}")->assertStatus(403);
        $this->actingAs($staff)->delete("/admin/asset-categories/{$category->id}")->assertStatus(403);
        $this->actingAs($staff)->delete("/admin/assets/{$asset->id}")->assertStatus(403);
    });

    test('inspector users are forbidden from all Master Aset endpoints', function (): void {
        $inspector = User::factory()->inspector()->create();

        $this->actingAs($inspector)->get('/admin/rooms')->assertStatus(403);
        $this->actingAs($inspector)->get('/admin/asset-categories')->assertStatus(403);
        $this->actingAs($inspector)->get('/admin/assets')->assertStatus(403);
    });
});

describe('data ruangan (rooms) management', function (): void {
    test('admin can view rooms and search by name or code', function (): void {
        $admin = User::factory()->admin()->create();
        Room::factory()->create(['name' => 'Lab Fisika', 'code' => 'LAB-FIS-1']);
        Room::factory()->create(['name' => 'Ruang Guru', 'code' => 'R-GURU']);

        $response = $this->actingAs($admin)->get('/admin/rooms');
        $response->assertStatus(200);
        $response->assertSee('Data Ruangan');
        $response->assertSee('Lab Fisika');
        $response->assertSee('Ruang Guru');

        $searchResponse = $this->actingAs($admin)->get('/admin/rooms?search=LAB-FIS');
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Lab Fisika');
        $searchResponse->assertDontSee('Ruang Guru');
    });

    test('admin can create a room successfully', function (): void {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/rooms', [
            'name' => 'Laboratorium Biologi',
            'code' => 'lab-bio-1',
        ]);

        $response->assertRedirect('/admin/rooms');
        $response->assertSessionHas('success');

        expect(Room::where('code', 'LAB-BIO-1')->first())->not->toBeNull()
            ->and(Room::where('code', 'LAB-BIO-1')->first()->name)->toBe('Laboratorium Biologi');
    });

    test('admin cannot create a room with duplicate code', function (): void {
        $admin = User::factory()->admin()->create();
        Room::factory()->create(['code' => 'LAB-1']);

        $response = $this->actingAs($admin)->post('/admin/rooms', [
            'name' => 'Another Room',
            'code' => 'LAB-1',
        ]);

        $response->assertSessionHasErrors(['code']);
    });

    test('admin can update a room', function (): void {
        $admin = User::factory()->admin()->create();
        $room = Room::factory()->create(['name' => 'Old Room Name', 'code' => 'OLD-RM']);

        $response = $this->actingAs($admin)->put("/admin/rooms/{$room->id}", [
            'name' => 'New Room Name',
            'code' => 'NEW-RM',
        ]);

        $response->assertRedirect('/admin/rooms');
        $room->refresh();
        expect($room->name)->toBe('New Room Name')
            ->and($room->code)->toBe('NEW-RM');
    });

    test('admin can delete an empty room', function (): void {
        $admin = User::factory()->admin()->create();
        $room = Room::factory()->create();

        $response = $this->actingAs($admin)->delete("/admin/rooms/{$room->id}");
        $response->assertRedirect('/admin/rooms');
        expect(Room::find($room->id))->toBeNull();
    });

    test('admin cannot delete a room that contains assets', function (): void {
        $admin = User::factory()->admin()->create();
        $room = Room::factory()->create();
        Asset::factory()->create(['room_id' => $room->id]);

        $response = $this->actingAs($admin)->delete("/admin/rooms/{$room->id}");
        $response->assertRedirect('/admin/rooms');
        $response->assertSessionHas('error');
        expect(Room::find($room->id))->not->toBeNull();
    });

    test('admin can view room create page with category options', function (): void {
        $admin = User::factory()->admin()->create();
        $c3 = Criteria::factory()->create(['code' => 'C3']);
        $subCriteria = SubCriteria::create([
            'criteria_id' => $c3->id,
            'description' => 'Ruang Laboratorium',
            'crisp_value' => 5,
        ]);

        $response = $this->actingAs($admin)->get('/admin/rooms/create');

        $response->assertOk();
        $response->assertViewIs('admin.rooms.create');
        $response->assertViewHas('subCriterias');
        $response->assertSee('Ruang Laboratorium');
    });

    test('admin can create a room with a valid sub_criteria_id', function (): void {
        $admin = User::factory()->admin()->create();
        $c3 = Criteria::factory()->create(['code' => 'C3']);
        $subCriteria = SubCriteria::create([
            'criteria_id' => $c3->id,
            'description' => 'Ruang Kelas',
            'crisp_value' => 5,
        ]);

        $response = $this->actingAs($admin)->post('/admin/rooms', [
            'name' => 'Kelas 7A',
            'code' => 'kls-7a',
            'sub_criteria_id' => $subCriteria->id,
        ]);

        $response->assertRedirect('/admin/rooms');
        $response->assertSessionHas('success');

        $room = Room::where('code', 'KLS-7A')->first();
        expect($room)->not->toBeNull()
            ->and($room->sub_criteria_id)->toBe($subCriteria->id);
    });

    test('validation fails when sub_criteria_id does not exist', function (): void {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/rooms', [
            'name' => 'Ruang Invalid',
            'code' => 'R-INV',
            'sub_criteria_id' => 999999,
        ]);

        $response->assertSessionHasErrors(['sub_criteria_id']);
    });

    test('admin can view room edit page with room data and category options', function (): void {
        $admin = User::factory()->admin()->create();
        $c3 = Criteria::factory()->create(['code' => 'C3']);
        $subCriteria = SubCriteria::create([
            'criteria_id' => $c3->id,
            'description' => 'Ruang Guru / Kantor',
            'crisp_value' => 3,
        ]);
        $room = Room::factory()->create(['sub_criteria_id' => $subCriteria->id]);

        $response = $this->actingAs($admin)->get("/admin/rooms/{$room->id}/edit");

        $response->assertOk();
        $response->assertViewIs('admin.rooms.edit');
        $response->assertViewHas('room', fn (Room $item): bool => $item->id === $room->id);
        $response->assertViewHas('subCriterias');
        $response->assertSee('Ruang Guru / Kantor');
    });

    test('admin can update a room sub_criteria_id', function (): void {
        $admin = User::factory()->admin()->create();
        $c3 = Criteria::factory()->create(['code' => 'C3']);
        $subCriteriaA = SubCriteria::create([
            'criteria_id' => $c3->id,
            'description' => 'Kategori A',
            'crisp_value' => 1,
        ]);
        $subCriteriaB = SubCriteria::create([
            'criteria_id' => $c3->id,
            'description' => 'Kategori B',
            'crisp_value' => 3,
        ]);
        $room = Room::factory()->create(['sub_criteria_id' => $subCriteriaA->id]);

        $response = $this->actingAs($admin)->put("/admin/rooms/{$room->id}", [
            'name' => $room->name,
            'code' => $room->code,
            'sub_criteria_id' => $subCriteriaB->id,
        ]);

        $response->assertRedirect('/admin/rooms');
        $room->refresh();
        expect($room->sub_criteria_id)->toBe($subCriteriaB->id);
    });

    test('admin sees room category description on index page', function (): void {
        $admin = User::factory()->admin()->create();
        $c3 = Criteria::factory()->create(['code' => 'C3']);
        $subCriteria = SubCriteria::create([
            'criteria_id' => $c3->id,
            'description' => 'Kategori Ruang Praktik',
            'crisp_value' => 5,
        ]);
        $roomWithCategory = Room::factory()->create([
            'name' => 'Lab Fisika Modern',
            'sub_criteria_id' => $subCriteria->id,
        ]);
        $roomWithoutCategory = Room::factory()->create([
            'name' => 'Gudang Belakang',
            'sub_criteria_id' => null,
        ]);

        $response = $this->actingAs($admin)->get('/admin/rooms');

        $response->assertOk();
        $response->assertSee('Kategori Ruang Praktik');
        $response->assertSee('-');
    });
});

describe('kategori aset (asset categories) management', function (): void {
    test('admin can view categories and search by name', function (): void {
        $admin = User::factory()->admin()->create();
        AssetCategory::factory()->create(['name' => 'Peralatan Olahraga', 'description' => 'Bola dan matras']);
        AssetCategory::factory()->create(['name' => 'Buku Perpustakaan', 'description' => 'Buku paket pelajaran']);

        $response = $this->actingAs($admin)->get('/admin/asset-categories');
        $response->assertStatus(200);
        $response->assertSee('Kategori Aset');
        $response->assertSee('Peralatan Olahraga');
        $response->assertSee('Buku Perpustakaan');

        $searchResponse = $this->actingAs($admin)->get('/admin/asset-categories?search=Olahraga');
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Peralatan Olahraga');
        $searchResponse->assertDontSee('Buku Perpustakaan');
    });

    test('admin can create a category successfully', function (): void {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/asset-categories', [
            'name' => 'Peralatan Multimedia',
            'description' => 'Proyektor, layar, dan speaker aktif',
        ]);

        $response->assertRedirect('/admin/asset-categories');
        $response->assertSessionHas('success');

        expect(AssetCategory::where('name', 'Peralatan Multimedia')->first())->not->toBeNull();
    });

    test('admin can update a category', function (): void {
        $admin = User::factory()->admin()->create();
        $category = AssetCategory::factory()->create(['name' => 'Nama Lama']);

        $response = $this->actingAs($admin)->put("/admin/asset-categories/{$category->id}", [
            'name' => 'Nama Baru',
            'description' => 'Deskripsi baru',
        ]);

        $response->assertRedirect('/admin/asset-categories');
        $category->refresh();
        expect($category->name)->toBe('Nama Baru');
    });

    test('admin can delete an unused category', function (): void {
        $admin = User::factory()->admin()->create();
        $category = AssetCategory::factory()->create();

        $response = $this->actingAs($admin)->delete("/admin/asset-categories/{$category->id}");
        $response->assertRedirect('/admin/asset-categories');
        expect(AssetCategory::find($category->id))->toBeNull();
    });

    test('admin cannot delete a category associated with assets', function (): void {
        $admin = User::factory()->admin()->create();
        $category = AssetCategory::factory()->create();
        Asset::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($admin)->delete("/admin/asset-categories/{$category->id}");
        $response->assertRedirect('/admin/asset-categories');
        $response->assertSessionHas('error');
        expect(AssetCategory::find($category->id))->not->toBeNull();
    });
});

describe('data aset (assets) management', function (): void {
    test('admin can view assets and filter by category, room, and condition', function (): void {
        $admin = User::factory()->admin()->create();
        $catA = AssetCategory::factory()->create(['name' => 'Elektronik']);
        $catB = AssetCategory::factory()->create(['name' => 'Mebel']);
        $roomA = Room::factory()->create(['name' => 'Lab 1']);
        $roomB = Room::factory()->create(['name' => 'Kelas 8B']);

        $asset1 = Asset::factory()->create([
            'name' => 'Komputer Guru',
            'code' => 'AST-PC-100',
            'category_id' => $catA->id,
            'room_id' => $roomA->id,
            'condition' => AssetCondition::Good,
        ]);

        $asset2 = Asset::factory()->create([
            'name' => 'Meja Murid',
            'code' => 'AST-MJ-200',
            'category_id' => $catB->id,
            'room_id' => $roomB->id,
            'condition' => AssetCondition::Damaged,
        ]);

        $response = $this->actingAs($admin)->get('/admin/assets');
        $response->assertStatus(200);
        $response->assertSee('Data Aset');
        $response->assertSee('AST-PC-100');
        $response->assertSee('AST-MJ-200');

        $filterCat = $this->actingAs($admin)->get("/admin/assets?category_id={$catA->id}");
        $filterCat->assertSee('AST-PC-100');
        $filterCat->assertDontSee('AST-MJ-200');

        $filterCond = $this->actingAs($admin)->get('/admin/assets?condition=damaged');
        $filterCond->assertSee('AST-MJ-200');
        $filterCond->assertDontSee('AST-PC-100');
    });

    test('admin can create an asset successfully', function (): void {
        $admin = User::factory()->admin()->create();
        $category = AssetCategory::factory()->create();
        $room = Room::factory()->create();

        $response = $this->actingAs($admin)->post('/admin/assets', [
            'code' => 'ast-proyektor-01',
            'name' => 'Proyektor Epson EB-X400',
            'category_id' => $category->id,
            'room_id' => $room->id,
            'procurement_year' => 2024,
            'condition' => AssetCondition::Good->value,
        ]);

        $response->assertRedirect('/admin/assets');
        $response->assertSessionHas('success');

        $asset = Asset::where('code', 'AST-PROYEKTOR-01')->first();
        expect($asset)->not->toBeNull()
            ->and($asset->name)->toBe('Proyektor Epson EB-X400')
            ->and($asset->condition)->toBe(AssetCondition::Good);
    });

    test('admin cannot create an asset with duplicate code', function (): void {
        $admin = User::factory()->admin()->create();
        $category = AssetCategory::factory()->create();
        $room = Room::factory()->create();
        Asset::factory()->create(['code' => 'AST-DUP-01']);

        $response = $this->actingAs($admin)->post('/admin/assets', [
            'code' => 'AST-DUP-01',
            'name' => 'Duplicated Asset',
            'category_id' => $category->id,
            'room_id' => $room->id,
            'procurement_year' => 2024,
            'condition' => AssetCondition::Good->value,
        ]);

        $response->assertSessionHasErrors(['code']);
    });

    test('admin can update an asset', function (): void {
        $admin = User::factory()->admin()->create();
        $asset = Asset::factory()->create(['name' => 'Old Asset Name']);
        $newRoom = Room::factory()->create();

        $response = $this->actingAs($admin)->put("/admin/assets/{$asset->id}", [
            'code' => $asset->code,
            'name' => 'Updated Asset Name',
            'category_id' => $asset->category_id,
            'room_id' => $newRoom->id,
            'procurement_year' => 2023,
            'condition' => AssetCondition::UnderRepair->value,
        ]);

        $response->assertRedirect('/admin/assets');
        $asset->refresh();
        expect($asset->name)->toBe('Updated Asset Name')
            ->and($asset->room_id)->toBe($newRoom->id)
            ->and($asset->condition)->toBe(AssetCondition::UnderRepair);
    });

    test('admin can delete an asset without repair tickets', function (): void {
        $admin = User::factory()->admin()->create();
        $asset = Asset::factory()->create();

        $response = $this->actingAs($admin)->delete("/admin/assets/{$asset->id}");
        $response->assertRedirect('/admin/assets');
        expect(Asset::find($asset->id))->toBeNull();
    });

    test('admin cannot delete an asset that has repair tickets', function (): void {
        $admin = User::factory()->admin()->create();
        $asset = Asset::factory()->create();
        $reporter = User::factory()->staff()->create();

        RepairTicket::create([
            'ticket_number' => 'TCK-TEST-999',
            'asset_id' => $asset->id,
            'reporter_id' => $reporter->id,
            'damage_description' => 'Layar proyektor redup',
            'status' => TicketStatus::PendingVerification,
        ]);

        $response = $this->actingAs($admin)->delete("/admin/assets/{$asset->id}");
        $response->assertRedirect('/admin/assets');
        $response->assertSessionHas('error');
        expect(Asset::find($asset->id))->not->toBeNull();
    });
});
