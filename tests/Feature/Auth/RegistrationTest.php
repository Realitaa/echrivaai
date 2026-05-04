<?php

use Laravel\Fortify\Features;
use App\Models\User;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register as student', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'student',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('student.classroom.index', absolute: false));
});

test('new users register as teacher will wait for admin approval', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'teacher',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('register.pending', absolute: false));
});

test('new users register as teacher cannot approve themselves', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'teacher',
        'is_approved' => true, // this will get ignored on controller store method
    ]);

    $this->assertAuthenticated();
    // unapproved registered teacher redirected to pending registration page
    $response->assertRedirect(route('register.pending', absolute: false));
});

test('approved teacher directed to classroom page when accessing pending registration page', function () {
    $user = User::factory()->create([
        'role' => 'teacher',
        'is_approved' => true,
    ]);

    $response = $this->actingAs($user)->get(route('register.pending', absolute: false));
    $response->assertRedirect(route('teacher.classroom.index', absolute: false));
});

test('new users register as admin will get rejected', function () {
    $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'admin',
    ])->assertForbidden();

    $this->assertDatabaseMissing('users', ['role' => 'admin']);
});