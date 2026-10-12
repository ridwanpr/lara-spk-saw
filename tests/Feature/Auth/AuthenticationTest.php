<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
    $response->assertSee('SIAset');
    $response->assertSee('Masuk ke Akun');
});

test('admin can authenticate using the login screen', function () {
    $admin = User::factory()->admin()->create([
        'password' => bcrypt('secret-pass'),
    ]);

    $response = $this->post('/login', [
        'email' => $admin->email,
        'password' => 'secret-pass',
    ]);

    $this->assertAuthenticatedAs($admin);
    $response->assertRedirect('/');
});

test('inspector can authenticate using the login screen', function () {
    $inspector = User::factory()->inspector()->create([
        'password' => bcrypt('inspector-pass'),
    ]);

    $response = $this->post('/login', [
        'email' => $inspector->email,
        'password' => 'inspector-pass',
    ]);

    $this->assertAuthenticatedAs($inspector);
    $response->assertRedirect('/');
});

test('staff can authenticate using the login screen', function () {
    $staff = User::factory()->staff()->create([
        'password' => bcrypt('staff-pass'),
    ]);

    $response = $this->post('/login', [
        'email' => $staff->email,
        'password' => 'staff-pass',
    ]);

    $this->assertAuthenticatedAs($staff);
    $response->assertRedirect('/');
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create([
        'password' => bcrypt('correct-password'),
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

test('public registration is disabled and returns 404', function () {
    $response = $this->get('/register');

    $response->assertStatus(404);
});
