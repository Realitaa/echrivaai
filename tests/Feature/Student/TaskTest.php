<?php

use App\Models\User;
use App\Models\Classroom;
use App\Models\Enrollment;
use App\Models\Task;
use Inertia\Testing\AssertableInertia as Assert;

// === Student/TaskController.index ===

test('student can view task list card inside an enrolled classroom', function () {
    $student = User::factory()->create(['role' => 'student']);
    $classroom = Classroom::factory()->create();
    
    Enrollment::factory()->create([
        'user_id' => $student->id,
        'classroom_id' => $classroom->id,
    ]);

    Task::factory()->count(3)->create([
        'classroom_id' => $classroom->id,
        'is_published' => true,
    ]);

    $this->actingAs($student)
        ->get(route('student.classroom.task.index', $classroom))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) =>
            $page->component('student/task/Index')
                 ->has('tasks.data', 3)
        );
});

test('student cannot view task list card inside an unenrolled classroom', function () {
    $student = User::factory()->create(['role' => 'student']);
    $classroom = Classroom::factory()->create();

    $this->actingAs($student)
        ->get(route('student.classroom.task.index', $classroom))
        ->assertForbidden();
});

test('student cannot view other student task list card', function () {
    $student1 = User::factory()->create(['role' => 'student']);
    $student2 = User::factory()->create(['role' => 'student']);
    
    $classroom1 = Classroom::factory()->create();
    $classroom2 = Classroom::factory()->create();
    
    Enrollment::factory()->create([
        'user_id' => $student1->id,
        'classroom_id' => $classroom1->id,
    ]);
    
    Enrollment::factory()->create([
        'user_id' => $student2->id,
        'classroom_id' => $classroom2->id,
    ]);

    $this->actingAs($student1)
        ->get(route('student.classroom.task.index', $classroom2))
        ->assertForbidden();
});

// ROUTE ACCESS

test('non-student cannot access student task route', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);

    $this->actingAs($teacher)
        ->get(route('student.classroom.task.index', $classroom))
        ->assertForbidden();
});

test('guest cannot access student task route', function () {
    $classroom = Classroom::factory()->create();

    $this->get(route('student.classroom.task.index', $classroom))
        ->assertRedirect('/login');
});
