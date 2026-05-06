<?php

use App\Models\User;

use Inertia\Testing\AssertableInertia as Assert;

test('admin can view dashboard with stats', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    
    User::factory()->count(2)->create(['role' => 'student']);
    User::factory()->count(3)->create(['role' => 'teacher', 'is_approved' => true]);
    User::factory()->count(1)->create(['role' => 'admin']);
    User::factory()->create(['role' => 'teacher', 'is_approved' => false]);

    $response = $this->actingAs($admin)->get(route('dashboard'));
    $response->assertSuccessful();
    
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/Dashboard')
        ->has('stats', fn (Assert $page) => $page
            ->where('admin', 2)
            ->where('teacher', 4)
            ->where('student', 2)
            ->where('unapproved_teacher', 1)
        )
        ->has('notApprovedTeacher')
    );
});

test('teacher can view dashboard with table of not approved teacher', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    
    $teacher = User::factory()->create(['role' => 'teacher', 'is_approved' => false]);

    $response = $this->actingAs($admin)->get(route('dashboard'));
    $response->assertSuccessful();
    
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/Dashboard')
        ->has('notApprovedTeacher', 1)
        ->has('notApprovedTeacher.0', fn (Assert $page) => $page
            ->where('id', $teacher->id)
            ->etc()
        )
    );
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