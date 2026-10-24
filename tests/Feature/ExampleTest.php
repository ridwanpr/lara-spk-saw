<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
});

test('authenticated users can access the dashboard with stats and criteria', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/');

    $response->assertStatus(200)
        ->assertViewIs('welcome')
        ->assertViewHasAll(['stats', 'topPriorities', 'priorities', 'criterias'])
        ->assertSeeText('Dashboard Prioritas Perbaikan');
});
