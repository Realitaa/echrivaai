<?php

use App\Models\User;
use App\Models\Classroom;
use App\Models\Task;
use App\Models\Enrollment;
use Inertia\Testing\AssertableInertia as Assert;

// === Admin/ClassroomController.index ===

test('admin can view classroom list table', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Classroom::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.classroom.index'))
        ->assertSuccessful()
        ->assertInertia(
            fn(Assert $page) => $page->component('admin/Classroom'),
        );
});

test('non-admin cannot access classroom list table', function () {
    $user = User::factory()->create(['role' => 'teacher']);

    $this->actingAs($user)
        ->get(route('admin.classroom.index'))
        ->assertForbidden();
});

test('admin can filter classroom by teacher_id', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $teacher1 = User::factory()->create(['role' => 'teacher']);
    $teacher2 = User::factory()->create(['role' => 'teacher']);

    Classroom::factory()->create([
        'name' => 'CLASS_A',
        'teacher_id' => $teacher1->id,
    ]);

    Classroom::factory()->create([
        'name' => 'CLASS_B',
        'teacher_id' => $teacher2->id,
    ]);

    $this->actingAs($admin)
        ->get(
            route('admin.classroom.index', [
                'teacher_id' => $teacher1->id,
            ]),
        )
        ->assertInertia(
            fn(Assert $page) => $page->where('classroom.data', function (
                $classroomroom,
            ) {
                $names = collect($classroomroom)->pluck('name');

                return $names->contains('CLASS_A') &&
                    !$names->contains('CLASS_B');
            }),
        );
});

test('admin can search classroom by name', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Classroom::factory()->create(['name' => 'Laravel Mastery']);
    Classroom::factory()->create(['name' => 'Vue Beginner']);

    $this->actingAs($admin)
        ->get(route('admin.classroom.index', ['search' => 'Laravel']))
        ->assertInertia(
            fn(Assert $page) => $page->where('classroom.data', function (
                $classroomroom,
            ) {
                $names = collect($classroomroom)->pluck('name');

                return $names->contains('Laravel Mastery') &&
                    !$names->contains('Vue Beginner');
            }),
        );
});

test('admin can search classroom by code', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Classroom::factory()->create([
        'name' => 'CLASS_X',
        'code' => 'ABC123',
    ]);

    Classroom::factory()->create([
        'name' => 'CLASS_Y',
        'code' => 'XYZ999',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.classroom.index', ['search' => 'ABC123']))
        ->assertInertia(
            fn(Assert $page) => $page->where('classroom.data', function (
                $classroomroom,
            ) {
                $codes = collect($classroomroom)->pluck('code');

                return $codes->contains('ABC123') &&
                    !$codes->contains('XYZ999');
            }),
        );
});

test('filter with invalid teacher_id returns empty result', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Classroom::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.classroom.index', ['teacher_id' => 999]))
        ->assertInertia(
            fn(Assert $page) => $page->where(
                'classroom.data',
                fn($classroomroom) => count($classroomroom) === 0,
            ),
        );
});

test('empty search returns all classroom', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Classroom::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.classroom.index', ['search' => '']))
        ->assertInertia(fn(Assert $page) => $page->has('classroom.data', 3));
});

test('pagination works correctly', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Classroom::factory()->count(15)->create();

    $this->actingAs($admin)
        ->get(route('admin.classroom.index'))
        ->assertInertia(fn(Assert $page) => $page->has('classroom.data', 10));
});

// === Admin/ClassroomController.destroy ===

test('admin can delete classroom', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $classroom = Classroom::factory()->create();

    $response = $this->actingAs($admin)->delete(
        route('admin.classroom.destroy', $classroom),
    );

    $response->assertRedirect(route('admin.classroom.index'));

    $response->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' => 'Classroom deleted successfully.',
    ]);

    $this->assertDatabaseMissing('classrooms', [
        'id' => $classroom->id,
    ]);
});

test('admin cannot delete class with active tasks', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $classroom = Classroom::factory()->create();
    Task::factory()->create([
        'classroom_id' => $classroom->id,
        'is_published' => true,
    ]);

    $response = $this->actingAs($admin)->delete(
        route('admin.classroom.destroy', $classroom),
    );

    $response->assertInertiaFlash('toast', [
        'type' => 'error',
        'message' => 'Classroom cannot be deleted because it has active tasks.',
    ]);

    $this->assertDatabaseHas('classrooms', [
        'id' => $classroom->id,
    ]);
});

test('non-admin cannot delete classroom', function () {
    $user = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create();

    $this->actingAs($user)
        ->delete(route('admin.classroom.destroy', $classroom))
        ->assertForbidden();
});

// === Admin/ClassroomController.enrollments ===

test('admin can view enrollments of a classroom', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $classroom = Classroom::factory()->create();

    $users = User::factory()->count(3)->create();

    foreach ($users as $user) {
        Enrollment::factory()->create([
            'classroom_id' => $classroom->id,
            'user_id' => $user->id,
        ]);
    }

    $response = $this->actingAs($admin)->get(
        route('admin.classroom.enrollments', $classroom),
    );

    $response->assertSuccessful()->assertJsonCount(3, 'data');
});

test('non-admin cannot view enrollments', function () {
    $user = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.classroom.enrollments', $classroom))
        ->assertForbidden();
});

test('enrollments include user data', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $classroom = Classroom::factory()->create();

    $user = User::factory()->create([
        'name' => 'TEST_USER',
    ]);

    Enrollment::factory()->create([
        'classroom_id' => $classroom->id,
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($admin)->get(
        route('admin.classroom.enrollments', $classroom),
    );

    $response->assertJsonFragment([
        'name' => 'TEST_USER',
    ]);
});
