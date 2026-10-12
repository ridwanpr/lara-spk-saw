<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function () {
    Route::get('/test-admin-only', fn () => 'admin-area')
        ->middleware(['web', 'auth', 'role:admin']);

    Route::get('/test-inspector-or-admin', fn () => 'inspector-or-admin-area')
        ->middleware(['web', 'auth', 'role:admin,inspector']);
});

test('guests accessing role protected routes are redirected to login', function () {
    $response = $this->get('/test-admin-only');

    $response->assertRedirect('/login');
});

test('admin can access admin-only route', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/test-admin-only');

    $response->assertStatus(200);
    $response->assertSee('admin-area');
});

test('staff is denied access to admin-only route', function () {
    $staff = User::factory()->staff()->create();

    $response = $this->actingAs($staff)->get('/test-admin-only');

    $response->assertStatus(403);
});

test('inspector is denied access to admin-only route', function () {
    $inspector = User::factory()->inspector()->create();

    $response = $this->actingAs($inspector)->get('/test-admin-only');

    $response->assertStatus(403);
});

test('admin and inspector can access multi-role route, but staff cannot', function () {
    $admin = User::factory()->admin()->create();
    $inspector = User::factory()->inspector()->create();
    $staff = User::factory()->staff()->create();

    $this->actingAs($admin)->get('/test-inspector-or-admin')->assertStatus(200);
    $this->actingAs($inspector)->get('/test-inspector-or-admin')->assertStatus(200);
    $this->actingAs($staff)->get('/test-inspector-or-admin')->assertStatus(403);
});
