<?php

use App\Models\User;

test('admin can view dashboard', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $response = $this->actingAs($admin)->get(route('dashboard'));
    $response->assertSuccessful();
});

test('non-admin cannot view dashboard', function () {
    $user = User::factory()->create([
        'role' => 'student',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));
    $response->assertForbidden();
});

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});