<?php

use App\Models\User;
use App\Models\Classroom;
use App\Models\Enrollment;
use Inertia\Testing\AssertableInertia as Assert;

// === Student/ClassroomController.index ===

test('student can view enrolled classroom list card', function () {
    $student = User::factory()->create(['role' => 'student']);
    $classroom = Classroom::factory()->create();

    Enrollment::factory()->create([
        'user_id' => $student->id,
        'classroom_id' => $classroom->id,
    ]);

    $this->actingAs($student)
        ->get(route('student.classroom.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) =>
            $page->component('student/classroom/Index')
                 ->has('classrooms.data', 1)
        );
});

test('student cannot view unenrolled classroom list card', function () {
    $student = User::factory()->create(['role' => 'student']);
    Classroom::factory()->create(['name' => 'UNENROLLED_CLASS']);

    $this->actingAs($student)
        ->get(route('student.classroom.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) =>
            $page->component('student/classroom/Index')
                 ->where('classrooms.data', function ($classrooms) {
                     $names = collect($classrooms)->pluck('name');
                     return !$names->contains('UNENROLLED_CLASS');
                 })
        );
});

test('student cannot view other student enrolled classroom', function () {
    $student1 = User::factory()->create(['role' => 'student']);
    $student2 = User::factory()->create(['role' => 'student']);

    $class1 = Classroom::factory()->create(['name' => 'CLASS_ONE']);
    $class2 = Classroom::factory()->create(['name' => 'CLASS_TWO']);

    Enrollment::factory()->create(['user_id' => $student1->id, 'classroom_id' => $class1->id]);
    Enrollment::factory()->create(['user_id' => $student2->id, 'classroom_id' => $class2->id]);

    $this->actingAs($student1)
        ->get(route('student.classroom.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) =>
            $page->where('classrooms.data', function ($classrooms) {
                $names = collect($classrooms)->pluck('name');
                return $names->contains('CLASS_ONE') && !$names->contains('CLASS_TWO');
            })
        );
});

// === Student/ClassroomController.enroll ===

test('student can enroll to a classroom', function () {
    $student = User::factory()->create(['role' => 'student']);
    $classroom = Classroom::factory()->create(['code' => 'VALIDCODE']);

    $response = $this->actingAs($student)
        ->post(route('student.classroom.enroll'), [
            'code' => 'VALIDCODE',
        ]);

    $response->assertRedirect(route('student.classroom.show', $classroom));
    $response->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' => 'Successfully enrolled in the classroom!',
    ]);

    $this->assertDatabaseHas('enrollments', [
        'user_id' => $student->id,
        'classroom_id' => $classroom->id,
    ]);
});

test('student cannot enroll other student to a classroom', function () {
    $student1 = User::factory()->create(['role' => 'student']);
    $student2 = User::factory()->create(['role' => 'student']);
    $classroom = Classroom::factory()->create(['code' => 'VALIDCODE']);

    // Attempting to inject user_id shouldn't work. The system must use auth()->id().
    $this->actingAs($student1)
        ->post(route('student.classroom.enroll'), [
            'code' => 'VALIDCODE',
            'user_id' => $student2->id,
        ]);

    $this->assertDatabaseMissing('enrollments', [
        'user_id' => $student2->id,
        'classroom_id' => $classroom->id,
    ]);
    
    $this->assertDatabaseHas('enrollments', [
        'user_id' => $student1->id,
        'classroom_id' => $classroom->id,
    ]);
});

test('student cannot enroll in the same classroom twice', function () {
    $student = User::factory()->create(['role' => 'student']);
    $classroom = Classroom::factory()->create(['code' => 'VALIDCODE']);

    Enrollment::factory()->create([
        'user_id' => $student->id,
        'classroom_id' => $classroom->id,
    ]);

    $response = $this->actingAs($student)
        ->post(route('student.classroom.enroll'), [
            'code' => 'VALIDCODE',
        ]);

    $response->assertRedirect(route('student.classroom.index'));

    $response->assertInertiaFlash('toast', [
        'type' => 'error',
        'message' => 'You are already enrolled in this classroom!',
    ]);

    $this->assertDatabaseCount('enrollments', 1);
});

test('student cannot enroll with an invalid classroom code', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)
        ->post(route('student.classroom.enroll'), [
            'code' => 'INVALID_CODE',
        ]);

    $response->assertRedirect(route('student.classroom.index'));

    $response->assertInertiaFlash('toast', [
        'type' => 'error',
        'message' => 'Invalid classroom code!',
    ]);
});

// === Student/ClassroomController.show ===

test('student can view classroom detail if they are enrolled', function () {
    $student = User::factory()->create(['role' => 'student']);
    $classroom = Classroom::factory()->create();

    Enrollment::factory()->create([
        'user_id' => $student->id,
        'classroom_id' => $classroom->id,
    ]);

    $this->actingAs($student)
        ->get(route('student.classroom.show', $classroom))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) =>
            $page->component('student/classroom/Show')
                 ->where('classroom.id', $classroom->id)
        );
});

test('student cannot view classroom detail if they are not enrolled', function () {
    $student = User::factory()->create(['role' => 'student']);
    $classroom = Classroom::factory()->create();

    $this->actingAs($student)
        ->get(route('student.classroom.show', $classroom))
        ->assertForbidden();
});

// ROUTE ACCESS

test('unauthenticated user cannot access student classroom endpoints', function () {
    $classroom = Classroom::factory()->create();

    $this->get(route('student.classroom.index'))->assertRedirect('/login');
    $this->post(route('student.classroom.enroll'), ['code' => 'CODE'])->assertRedirect('/login');
    $this->get(route('student.classroom.show', $classroom))->assertRedirect('/login');
});
