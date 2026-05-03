<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

// === UserController.index ===
test('admin can view user list', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.user.index'))->assertSuccessful();
});

test('non-admin cannot access user management', function () {
    $user = User::factory()->create(['role' => 'teacher']);

    $this->actingAs($user)->get(route('admin.user.index'))->assertForbidden();
});

test('admin can filter users by role', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    User::factory()->create([
        'name' => 'TEACHER_USER',
        'role' => 'teacher',
    ]);

    User::factory()->create([
        'name' => 'STUDENT_USER',
        'role' => 'student',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.user.index', ['role' => 'teacher']))
        ->assertInertia(
            fn(Assert $page) => $page->where('users.data', function ($users) {
                $names = collect($users)->pluck('name');

                return $names->contains('TEACHER_USER') &&
                    !$names->contains('STUDENT_USER');
            }),
        );
});

// Use case: admin dapat memilih pengguna yang mendaftar sebagai "teacher" namun belum di approve
test('admin can filter users by role and approval', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    User::factory()->create([
        'name' => 'APPROVED_USER',
        'role' => 'teacher',
        'is_approved' => true,
    ]);

    User::factory()->create([
        'name' => 'NOT_APPROVED_USER',
        'role' => 'teacher',
        'is_approved' => false,
    ]);

    $this->actingAs($admin)
        ->get(
            route('admin.user.index', [
                'role' => 'teacher',
                'is_approved' => 0,
            ]),
        )
        ->assertInertia(
            fn(Assert $page) => $page
                ->component('admin/Users')
                ->where('users.data', function ($users) {
                    return collect($users)
                        ->pluck('name')
                        ->contains('NOT_APPROVED_USER') &&
                        !collect($users)
                            ->pluck('name')
                            ->contains('APPROVED_USER');
                }),
        );
});

test('admin can search users by email', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    User::factory()->create([
        'email' => 'findme@test.com',
    ]);

    User::factory()->create([
        'email' => 'other@test.com',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.user.index', ['search' => 'findme']))
        ->assertInertia(
            fn(Assert $page) => $page->where('users.data', function ($users) {
                $emails = collect($users)->pluck('email');

                return $emails->contains('findme@test.com') &&
                    !$emails->contains('other@test.com');
            }),
        );
});

test('empty search return all users', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->count(15)->create();

    $this->actingAs($admin)
        ->get(route('admin.user.index', ['search' => '']))
        ->assertInertia(fn(Assert $page) => $page->has('users.data', 10));
});

test('pagination works correctly', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->count(15)->create();

    $this->actingAs($admin)
        ->get(route('admin.user.index'))
        ->assertInertia(fn(Assert $page) => $page->has('users.data', 10));
});

// === UserController.store ===
test('admin can create user', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post(route('admin.user.store'), [
        'name' => 'New User',
        'email' => 'exampleemail@example.com',
        'password' => 'password',
        'role' => 'student',
    ]);

    $response->assertRedirect(route('admin.user.index'));

    $response->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' => 'User created successfully.',
    ]);

    $this->assertDatabaseHas('users', [
        'email' => 'exampleemail@example.com',
        'role' => 'student',
        'is_approved' => true,
    ]);
});

test('email must be unique when creating', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->create(['email' => 'test@mail.com']);

    $this->actingAs($admin)
        ->post(route('admin.user.store'), [
            'name' => 'Test',
            'email' => 'test@mail.com',
            'password' => 'password',
            'role' => 'teacher',
        ])
        ->assertSessionHasErrors('email');
});

test('auto approve teacher created by admin', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post(route('admin.user.store'), [
        'name' => 'New Teacher',
        'email' => 'exampleemail@example.com',
        'password' => 'password',
        'role' => 'teacher',
    ]);

    $response->assertRedirect(route('admin.user.index'));

    $response->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' => 'User created successfully.',
    ]);

    $this->assertDatabaseHas('users', [
        'email' => 'exampleemail@example.com',
        'role' => 'teacher',
        'is_approved' => true,
    ]);
});

test('non-admin cannot create user', function () {
    $user = User::factory()->create(['role' => 'teacher']);

    $this->actingAs($user)
        ->post(route('admin.user.store'), [])
        ->assertForbidden();
});

// === UserController.update ===
test('admin can update user', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'teacher']);

    $response = $this->actingAs($admin)->put(
        route('admin.user.update', $user),
        [
            'name' => 'Updated Name',
            'email' => 'exampleemail2@example.com',
            'password' => 'newpassword',
            'role' => 'student',
            'is_approved' => false,
        ],
    );

    $response->assertRedirect(route('admin.user.index'));

    $response->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' => 'User updated successfully.',
    ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Name',
        'email' => 'exampleemail2@example.com',
        'role' => 'student',
        'is_approved' => false,
    ]);
});

test('email must be unique when updating', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->create(['email' => 'a@test.com']); // $user1
    $user2 = User::factory()->create(['email' => 'b@test.com']);

    $this->actingAs($admin)
        ->put(route('admin.user.update', $user2), [
            'email' => 'a@test.com',
        ])
        ->assertSessionHasErrors('email');
});

test('update without password does not change password', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create([
        'password' => bcrypt('oldpassword'),
    ]);

    $response = $this->actingAs($admin)->put(
        route('admin.user.update', $user),
        [
            'name' => 'Updated Name',
        ],
    );

    $response->assertRedirect(route('admin.user.index'));

    $response->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' => 'User updated successfully.',
    ]);

    expect($user->fresh()->password)->toBe($user->password);
});

test('non-admin cannot update user', function () {
    $user = User::factory()->create(['role' => 'teacher']);

    $this->actingAs($user)
        ->put(route('admin.user.update', $user), [])
        ->assertForbidden();
});

// === UserController.destroy ===
test('admin can delete user', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'teacher']);

    $response = $this->actingAs($admin)->delete(
        route('admin.user.destroy', $user),
    );

    $response->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' => 'User deleted successfully.',
    ]);

    $response->assertRedirect(route('admin.user.index'));
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test("admin can't self delete", function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->delete(
        route('admin.user.destroy', $admin),
    );

    $response->assertRedirect(route('admin.user.index'));

    $response->assertInertiaFlash('toast', [
        'type' => 'error',
        'message' => 'You cannot delete yourself.',
    ]);

    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});

test('non-admin cannot delete user', function () {
    $user = User::factory()->create(['role' => 'teacher']);

    $this->actingAs($user)
        ->delete(route('admin.user.destroy', $user))
        ->assertForbidden();
});

// === UserController.approve ===
test('admin can toggle user approval', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create([
        'role' => 'teacher',
        'is_approved' => false,
    ]);

    $response = $this->actingAs($admin)->patch(
        route('admin.user.approve', $user),
    );

    $response->assertRedirect(route('admin.user.index'));

    $response->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' => 'Teacher registration approved successfully.',
    ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'is_approved' => true,
    ]);
});

test('toggle approval twice returns to original state', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['is_approved' => false]);

    $this->actingAs($admin)->patch(route('admin.user.approve', $user));

    $this->actingAs($admin)->patch(route('admin.user.approve', $user));

    expect($user->fresh()->is_approved)->toBeFalse();
});

test('non-admin cannot toggle user approval', function () {
    $user = User::factory()->create(['role' => 'teacher']);

    $this->actingAs($user)
        ->patch(route('admin.user.approve', $user))
        ->assertForbidden();
});
