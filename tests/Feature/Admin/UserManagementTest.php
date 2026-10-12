<?php

use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
});

describe('broken role access protection', function (): void {
    test('unauthenticated guests are redirected to login', function (): void {
        $user = User::factory()->create();

        $this->get('/admin/users')->assertRedirect('/login');
        $this->get('/admin/users/create')->assertRedirect('/login');
        $this->post('/admin/users', [])->assertRedirect('/login');
        $this->get("/admin/users/{$user->id}/edit")->assertRedirect('/login');
        $this->put("/admin/users/{$user->id}", [])->assertRedirect('/login');
        $this->delete("/admin/users/{$user->id}")->assertRedirect('/login');
    });

    test('staff users are forbidden from all user management endpoints', function (): void {
        $staff = User::factory()->staff()->create();
        $targetUser = User::factory()->create();

        $this->actingAs($staff)->get('/admin/users')->assertStatus(403);
        $this->actingAs($staff)->get('/admin/users/create')->assertStatus(403);
        $this->actingAs($staff)->post('/admin/users', [
            'name' => 'Hacker User',
            'email' => 'hacker@example.com',
            'role_id' => $staff->role_id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertStatus(403);
        $this->actingAs($staff)->get("/admin/users/{$targetUser->id}/edit")->assertStatus(403);
        $this->actingAs($staff)->put("/admin/users/{$targetUser->id}", [
            'name' => 'Updated Name',
            'email' => $targetUser->email,
            'role_id' => $staff->role_id,
        ])->assertStatus(403);
        $this->actingAs($staff)->delete("/admin/users/{$targetUser->id}")->assertStatus(403);
    });

    test('inspector users are forbidden from all user management endpoints', function (): void {
        $inspector = User::factory()->inspector()->create();
        $targetUser = User::factory()->create();

        $this->actingAs($inspector)->get('/admin/users')->assertStatus(403);
        $this->actingAs($inspector)->get('/admin/users/create')->assertStatus(403);
        $this->actingAs($inspector)->post('/admin/users', [])->assertStatus(403);
        $this->actingAs($inspector)->get("/admin/users/{$targetUser->id}/edit")->assertStatus(403);
        $this->actingAs($inspector)->put("/admin/users/{$targetUser->id}", [])->assertStatus(403);
        $this->actingAs($inspector)->delete("/admin/users/{$targetUser->id}")->assertStatus(403);
    });
});

describe('admin user management features', function (): void {
    test('admin can view user list', function (): void {
        $admin = User::factory()->admin()->create(['name' => 'Super Admin']);
        $staff = User::factory()->staff()->create(['name' => 'Guru Matematika']);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertSee('Data Pengguna');
        $response->assertSee('Super Admin');
        $response->assertSee('Guru Matematika');
    });

    test('admin can search users by name and email', function (): void {
        $admin = User::factory()->admin()->create();
        $userA = User::factory()->create(['name' => 'Bambang Sudirman', 'email' => 'bambang@sekolah.sch.id']);
        $userB = User::factory()->create(['name' => 'Siti Nurhaliza', 'email' => 'siti@sekolah.sch.id']);

        $response = $this->actingAs($admin)->get('/admin/users?search=Bambang');
        $response->assertStatus(200);
        $response->assertSee('Bambang Sudirman');
        $response->assertDontSee('Siti Nurhaliza');

        $responseEmail = $this->actingAs($admin)->get('/admin/users?search=siti@sekolah.sch.id');
        $responseEmail->assertStatus(200);
        $responseEmail->assertSee('Siti Nurhaliza');
        $responseEmail->assertDontSee('Bambang Sudirman');
    });

    test('admin can filter users by role', function (): void {
        $admin = User::factory()->admin()->create(['name' => 'Admin Boss']);
        $inspectorRole = Role::where('name', RoleName::Inspector->value)->first();
        $staffRole = Role::where('name', RoleName::Staff->value)->first();

        $inspectorUser = User::factory()->inspector()->create(['name' => 'Inspector Sarpras']);
        $staffUser = User::factory()->staff()->create(['name' => 'Staff Biasa']);

        $response = $this->actingAs($admin)->get("/admin/users?role_id={$inspectorRole->id}");

        $response->assertStatus(200);
        $response->assertSee('Inspector Sarpras');
        $response->assertDontSee('Staff Biasa');
    });

    test('admin can access create user page', function (): void {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/users/create');

        $response->assertStatus(200);
        $response->assertSee('Tambah Pengguna Baru');
        $response->assertSee('Formulir Informasi Pengguna');
    });

    test('admin can store a new user successfully', function (): void {
        $admin = User::factory()->admin()->create();
        $inspectorRole = Role::where('name', RoleName::Inspector->value)->first();

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Indra Wijaya',
            'email' => 'indra@sekolah.sch.id',
            'role_id' => $inspectorRole->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');

        $createdUser = User::where('email', 'indra@sekolah.sch.id')->first();
        expect($createdUser)->not->toBeNull()
            ->and($createdUser->name)->toBe('Indra Wijaya')
            ->and($createdUser->role_id)->toBe($inspectorRole->id)
            ->and($createdUser->isInspector())->toBeTrue()
            ->and(Hash::check('password123', $createdUser->password))->toBeTrue();
    });

    test('admin cannot store a user with duplicate email', function (): void {
        $admin = User::factory()->admin()->create();
        $existing = User::factory()->create(['email' => 'existing@example.com']);
        $staffRole = Role::where('name', RoleName::Staff->value)->first();

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'New Guy',
            'email' => 'existing@example.com',
            'role_id' => $staffRole->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    });

    test('admin can view edit user page', function (): void {
        $admin = User::factory()->admin()->create();
        $targetUser = User::factory()->staff()->create(['name' => 'Target Guru']);

        $response = $this->actingAs($admin)->get("/admin/users/{$targetUser->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Ubah Data Pengguna');
        $response->assertSee('Target Guru');
    });

    test('admin can update user info without changing password', function (): void {
        $admin = User::factory()->admin()->create();
        $staffRole = Role::where('name', RoleName::Staff->value)->first();
        $inspectorRole = Role::where('name', RoleName::Inspector->value)->first();

        $originalHash = Hash::make('original-password');
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'role_id' => $staffRole->id,
            'password' => $originalHash,
        ]);

        $response = $this->actingAs($admin)->put("/admin/users/{$user->id}", [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'role_id' => $inspectorRole->id,
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');

        $user->refresh();
        expect($user->name)->toBe('New Name')
            ->and($user->email)->toBe('new@example.com')
            ->and($user->role_id)->toBe($inspectorRole->id)
            ->and($user->password)->toBe($originalHash);
    });

    test('admin can update user password', function (): void {
        $admin = User::factory()->admin()->create();
        $staffRole = Role::where('name', RoleName::Staff->value)->first();

        $user = User::factory()->create([
            'role_id' => $staffRole->id,
            'password' => Hash::make('old-secret'),
        ]);

        $response = $this->actingAs($admin)->put("/admin/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'role_id' => $user->role_id,
            'password' => 'new-secret-123',
            'password_confirmation' => 'new-secret-123',
        ]);

        $response->assertRedirect('/admin/users');
        $user->refresh();
        expect(Hash::check('new-secret-123', $user->password))->toBeTrue();
    });

    test('admin can delete another user', function (): void {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->delete("/admin/users/{$user->id}");

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');
        expect(User::find($user->id))->toBeNull();
    });

    test('admin cannot delete their own account', function (): void {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->delete("/admin/users/{$admin->id}");

        $response->assertStatus(403);
        expect(User::find($admin->id))->not->toBeNull();
    });
});
