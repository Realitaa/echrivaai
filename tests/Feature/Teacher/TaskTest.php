<?php

use App\Models\User;
use App\Models\Classroom;
use App\Models\Task;
use App\Models\Submission;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

// === INDEX ===

test('teacher can view task list card inside a classroom', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    
    Task::factory()->count(3)->create([
        'classroom_id' => $classroom->id,
        'created_by' => $teacher->id
    ]);

    $this->actingAs($teacher)
        ->get(route('teacher.classroom.task.index', $classroom))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) =>
            $page->component('teacher/task/Index')
                 ->has('tasks.data', 3)
        );
});

test('teacher cannot view task list from other classroom teacher', function () {
    $teacher1 = User::factory()->create(['role' => 'teacher']);
    $teacher2 = User::factory()->create(['role' => 'teacher']);

    $classroom2 = Classroom::factory()->create(['teacher_id' => $teacher2->id]);

    $this->actingAs($teacher1)
        ->get(route('teacher.classroom.task.index', $classroom2))
        ->assertForbidden();
});

// === STORE ===

test('teacher can create task with complete attributes', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);

    $deadline = Carbon::now()->addDays(7)->toDateTimeString();

    $response = $this->actingAs($teacher)
        ->post(route('teacher.classroom.task.store', $classroom), [
            'title' => 'Complete Task',
            'description' => 'This is a complete description',
            'deadline' => $deadline,
            'is_published' => true,
        ]);

    $response->assertRedirect(route('teacher.classroom.show', $classroom));
    $response->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' => 'Task created successfully!',
    ]);

    $this->assertDatabaseHas('tasks', [
        'classroom_id' => $classroom->id,
        'title' => 'Complete Task',
        'created_by' => $teacher->id,
        'is_published' => true,
    ]);
});

test('teacher can create task with only title and deadline (draft)', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);

    $deadline = Carbon::now()->addDays(3)->toDateTimeString();

    $response = $this->actingAs($teacher)
        ->post(route('teacher.classroom.task.store', $classroom), [
            'title' => 'Minimal Task',
            'deadline' => $deadline,
        ]);

    $response->assertRedirect(route('teacher.classroom.show', $classroom));
    $response->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' => 'Task created successfully!',
    ]);

    $this->assertDatabaseHas('tasks', [
        'title' => 'Minimal Task',
        'is_published' => false, // Default dari migration
    ]);
});

test('teacher cannot create task without title', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);

    $this->actingAs($teacher)
        ->post(route('teacher.classroom.task.store', $classroom), [
            'deadline' => Carbon::now()->addDays(3)->toDateTimeString(),
        ])
        ->assertSessionHasErrors('title');
});

test('teacher cannot create task without deadline', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);

    $this->actingAs($teacher)
        ->post(route('teacher.classroom.task.store', $classroom), [
            'title' => 'Task without deadline',
        ])
        ->assertSessionHasErrors('deadline');
});

test('teacher cannot create task with invalid title format', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);

    $this->actingAs($teacher)
        ->post(route('teacher.classroom.task.store', $classroom), [
            'title' => '   ', 
            'deadline' => Carbon::now()->addDays(3)->toDateTimeString(),
        ])
        ->assertSessionHasErrors('title');
});

test('teacher cannot create task with past date deadline', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);

    $this->actingAs($teacher)
        ->post(route('teacher.classroom.task.store', $classroom), [
            'title' => 'Past Deadline Task',
            'deadline' => Carbon::now()->subDays(1)->toDateTimeString(),
        ])
        ->assertSessionHasErrors('deadline');
});

test('teacher cannot create task with invalid deadline format', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);

    $this->actingAs($teacher)
        ->post(route('teacher.classroom.task.store', $classroom), [
            'title' => 'Invalid Deadline Format',
            'deadline' => 'bukan-tanggal',
        ])
        ->assertSessionHasErrors('deadline');
});

test('teacher cannot create task in other teacher classroom', function () {
    $teacher1 = User::factory()->create(['role' => 'teacher']);
    $teacher2 = User::factory()->create(['role' => 'teacher']);
    $classroom2 = Classroom::factory()->create(['teacher_id' => $teacher2->id]);

    $this->actingAs($teacher1)
        ->post(route('teacher.classroom.task.store', $classroom2), [
            'title' => 'Hacker Task',
            'deadline' => Carbon::now()->addDays(3)->toDateTimeString(),
        ])
        ->assertForbidden();
});

// === UPDATE ===

test('teacher can update task attributes', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $task = Task::factory()->create(['classroom_id' => $classroom->id, 'created_by' => $teacher->id]);

    $newDeadline = Carbon::now()->addDays(10)->toDateTimeString();

    $response = $this->actingAs($teacher)
        ->put(route('teacher.classroom.task.update', [$classroom, $task]), [
            'title' => 'Updated Task Title',
            'description' => 'Updated description',
            'deadline' => $newDeadline,
            'is_published' => true,
        ]);

    $response->assertRedirect(route('teacher.classroom.show', $classroom));
    $response->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' => 'Task updated successfully!',
    ]);

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'title' => 'Updated Task Title',
        'deadline' => $newDeadline,
        'is_published' => true,
    ]);
});

test('teacher cannot update task without title', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $task = Task::factory()->create(['classroom_id' => $classroom->id, 'created_by' => $teacher->id]);

    $this->actingAs($teacher)
        ->put(route('teacher.classroom.task.update', [$classroom, $task]), [
            'title' => '',
            'deadline' => Carbon::now()->addDays(3)->toDateTimeString(),
        ])
        ->assertSessionHasErrors('title');
});

test('teacher cannot update task with invalid title format', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $task = Task::factory()->create(['classroom_id' => $classroom->id, 'created_by' => $teacher->id]);

    $this->actingAs($teacher)
        ->put(route('teacher.classroom.task.update', [$classroom, $task]), [
            'title' => '   ',
            'deadline' => Carbon::now()->addDays(3)->toDateTimeString(),
        ])
        ->assertSessionHasErrors('title');
});

test('teacher cannot update other teacher classroom task', function () {
    $teacher1 = User::factory()->create(['role' => 'teacher']);
    $teacher2 = User::factory()->create(['role' => 'teacher']);
    $classroom2 = Classroom::factory()->create(['teacher_id' => $teacher2->id]);
    $task2 = Task::factory()->create(['classroom_id' => $classroom2->id, 'created_by' => $teacher2->id]);

    $this->actingAs($teacher1)
        ->put(route('teacher.classroom.task.update', [$classroom2, $task2]), [
            'title' => 'Hacked Title',
            'deadline' => Carbon::now()->addDays(3)->toDateTimeString(),
        ])
        ->assertForbidden();
});

test('teacher cannot update deadline with past date', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $task = Task::factory()->create(['classroom_id' => $classroom->id, 'created_by' => $teacher->id]);

    $this->actingAs($teacher)
        ->put(route('teacher.classroom.task.update', [$classroom, $task]), [
            'title' => 'Updated Task Title',
            'description' => 'Updated description',
            'deadline' => Carbon::now()->subDays(3)->toDateTimeString(),
        ])
        ->assertSessionHasErrors('deadline');
});

test('teacher cannot update deadline with invalid date format', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $task = Task::factory()->create(['classroom_id' => $classroom->id, 'created_by' => $teacher->id]);

    $this->actingAs($teacher)
        ->put(route('teacher.classroom.task.update', [$classroom, $task]), [
            'title' => 'Updated Task Title',
            'description' => 'Updated description',
            'deadline' => 'bukan-tanggal',
        ])
        ->assertSessionHasErrors('deadline');
});

test('teacher cannot update other teacher tasks', function () {
    $teacher1 = User::factory()->create(['role' => 'teacher']);
    $teacher2 = User::factory()->create(['role' => 'teacher']);
    $classroom2 = Classroom::factory()->create(['teacher_id' => $teacher2->id]);
    $task2 = Task::factory()->create(['classroom_id' => $classroom2->id, 'created_by' => $teacher2->id]);

    $this->actingAs($teacher1)
        ->put(route('teacher.classroom.task.update', [$classroom2, $task2]), [
            'title' => 'Updated Task Title',
            'description' => 'Updated description',
            'deadline' => Carbon::now()->addDays(3)->toDateTimeString(),
        ])
        ->assertForbidden();
});

// === SHOW ===

test('teacher can view task details', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $task = Task::factory()->create(['classroom_id' => $classroom->id, 'created_by' => $teacher->id]);

    $this->actingAs($teacher)
        ->get(route('teacher.classroom.task.show', [$classroom, $task]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) =>
            $page->component('teacher/task/Show')
                 ->where('task.id', $task->id)
        );
});

test('teacher cannot view task details from other classroom teacher', function () {
    $teacher1 = User::factory()->create(['role' => 'teacher']);
    $teacher2 = User::factory()->create(['role' => 'teacher']);
    $classroom2 = Classroom::factory()->create(['teacher_id' => $teacher2->id]);
    $task2 = Task::factory()->create(['classroom_id' => $classroom2->id, 'created_by' => $teacher2->id]);

    $this->actingAs($teacher1)
        ->get(route('teacher.classroom.task.show', [$classroom2, $task2]))
        ->assertForbidden();
});


// === DESTROY ===

test('teacher can delete task', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $task = Task::factory()->create(['classroom_id' => $classroom->id, 'created_by' => $teacher->id]);

    $response = $this->actingAs($teacher)
        ->delete(route('teacher.classroom.task.destroy', [$classroom, $task]));

    $response->assertRedirect(route('teacher.classroom.index'));
    $response->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' => 'Task deleted successfully!',
    ]);

    $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
});

test('teacher cannot delete other teacher task', function () {
    $teacher1 = User::factory()->create(['role' => 'teacher']);
    $teacher2 = User::factory()->create(['role' => 'teacher']);
    $classroom2 = Classroom::factory()->create(['teacher_id' => $teacher2->id]);
    $task2 = Task::factory()->create(['classroom_id' => $classroom2->id, 'created_by' => $teacher2->id]);

    $this->actingAs($teacher1)
        ->delete(route('teacher.classroom.task.destroy', [$classroom2, $task2]))
        ->assertForbidden();
});

test('teacher cannot delete task that already has submissions', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $task = Task::factory()->create(['classroom_id' => $classroom->id, 'created_by' => $teacher->id]);
    
    // Asumsi ada submission
    Submission::factory()->create(['task_id' => $task->id]);

    $response = $this->actingAs($teacher)
        ->delete(route('teacher.classroom.task.destroy', [$classroom, $task]));

    $response->assertRedirect(route('teacher.classroom.show', $classroom));
    $response->assertInertiaFlash('toast', [
        'type' => 'error',
        'message' => 'Task cannot be deleted because it has submissions!',
    ]);
    
    $this->assertDatabaseHas('tasks', ['id' => $task->id]);
});

// === ROUTE ACCESS ===

test('non-teacher cannot access teacher task routes', function () {
    $student = User::factory()->create(['role' => 'student']);
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $task = Task::factory()->create(['classroom_id' => $classroom->id, 'created_by' => $teacher->id]);

    $this->actingAs($student)
        ->post(route('teacher.classroom.task.store', $classroom), [])
        ->assertForbidden();
        
    $this->actingAs($student)
        ->put(route('teacher.classroom.task.update', [$classroom, $task]), [])
        ->assertForbidden();
});

test('guest cannot access teacher task routes', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);

    $this->get(route('teacher.classroom.task.index', $classroom))
        ->assertRedirect('/login');
});