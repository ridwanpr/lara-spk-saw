<?php

use App\Models\Criteria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to login when visiting documentation', function () {
    $response = $this->get('/documentation');

    $response->assertRedirect('/login');
});

test('authenticated users can access documentation page', function () {
    $user = User::factory()->create();
    $criteria = Criteria::factory()->create([
        'code' => 'C1',
        'name' => 'Tingkat Kerusakan Fisik',
    ]);

    $response = $this->actingAs($user)->get('/documentation');

    $response->assertStatus(200)
        ->assertViewIs('documentation')
        ->assertViewHas('criterias')
        ->assertSeeText('Dokumentasi Aplikasi')
        ->assertSeeText('Metode Simple Additive Weighting (SAW)')
        ->assertSeeText('Tingkat Kerusakan Fisik');
});
