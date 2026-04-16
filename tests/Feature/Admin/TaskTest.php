<?php

use App\Models\User;
use App\Models\Classroom;
use App\Models\Task;
use App\Models\Submission;
use Inertia\Testing\AssertableInertia as Assert;

// === Admin/TaskController.index ===

test('admin can view task list table', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Task::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.task.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) =>
            $page->component('admin/Task')
        );
});

test('non-admin cannot access task list table', function () {
    $user = User::factory()->create(['role' => 'teacher']);

    $this->actingAs($user)
        ->get(route('admin.task.index'))
        ->assertForbidden();
});

// === SEARCH ===

test('admin can search task by title', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Task::factory()->create(['title' => 'Essay AI']);
    Task::factory()->create(['title' => 'Math Assignment']);

    $this->actingAs($admin)
        ->get(route('admin.task.index', ['search' => 'Essay']))
        ->assertInertia(fn (Assert $page) =>
            $page->where('tasks.data', function ($tasks) {
                $titles = collect($tasks)->pluck('title');

                return $titles->contains('Essay AI')
                    && !$titles->contains('Math Assignment');
            })
        );
});

test('admin can search task by teacher name', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $teacher1 = User::factory()->create(['name' => 'Teacher One']);
    $teacher2 = User::factory()->create(['name' => 'Teacher Two']);

    $class1 = Classroom::factory()->create(['teacher_id' => $teacher1->id]);
    $class2 = Classroom::factory()->create(['teacher_id' => $teacher2->id]);

    Task::factory()->create([
        'title' => 'Task A',
        'classroom_id' => $class1->id,
    ]);

    Task::factory()->create([
        'title' => 'Task B',
        'classroom_id' => $class2->id,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.task.index', ['search' => 'Teacher One']))
        ->assertInertia(fn (Assert $page) =>
            $page->where('tasks.data', function ($tasks) {
                $titles = collect($tasks)->pluck('title');

                return $titles->contains('Task A')
                    && !$titles->contains('Task B');
            })
        );
});

test('admin can search task by classroom name', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $class1 = Classroom::factory()->create(['name' => 'Laravel Class']);
    $class2 = Classroom::factory()->create(['name' => 'Vue Class']);

    Task::factory()->create([
        'title' => 'Task A',
        'classroom_id' => $class1->id,
    ]);

    Task::factory()->create([
        'title' => 'Task B',
        'classroom_id' => $class2->id,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.task.index', ['search' => 'Laravel']))
        ->assertInertia(fn (Assert $page) =>
            $page->where('tasks.data', function ($tasks) {
                $titles = collect($tasks)->pluck('title');

                return $titles->contains('Task A')
                    && !$titles->contains('Task B');
            })
        );
});

test('empty search returns all tasks', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Task::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.task.index', ['search' => '']))
        ->assertInertia(fn (Assert $page) =>
            $page->has('tasks.data', 3)
        );
});

// === FILTER ===

test('admin can filter tasks by teacher_id', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $teacher1 = User::factory()->create();
    $teacher2 = User::factory()->create();

    $class1 = Classroom::factory()->create(['teacher_id' => $teacher1->id]);
    $class2 = Classroom::factory()->create(['teacher_id' => $teacher2->id]);

    Task::factory()->create(['title' => 'Task A', 'classroom_id' => $class1->id]);
    Task::factory()->create(['title' => 'Task B', 'classroom_id' => $class2->id]);

    $this->actingAs($admin)
        ->get(route('admin.task.index', ['teacher_id' => $teacher1->id]))
        ->assertInertia(fn (Assert $page) =>
            $page->where('tasks.data', function ($tasks) {
                $titles = collect($tasks)->pluck('title');

                return $titles->contains('Task A')
                    && !$titles->contains('Task B');
            })
        );
});

test('admin can filter tasks by classroom_id', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $class1 = Classroom::factory()->create();
    $class2 = Classroom::factory()->create();

    Task::factory()->create(['title' => 'Task A', 'classroom_id' => $class1->id]);
    Task::factory()->create(['title' => 'Task B', 'classroom_id' => $class2->id]);

    $this->actingAs($admin)
        ->get(route('admin.task.index', ['classroom_id' => $class1->id]))
        ->assertInertia(fn (Assert $page) =>
            $page->where('tasks.data', function ($tasks) {
                $titles = collect($tasks)->pluck('title');

                return $titles->contains('Task A')
                    && !$titles->contains('Task B');
            })
        );
});

test('admin can filter tasks by status (is_published)', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Task::factory()->create(['title' => 'Published Task', 'is_published' => true]);
    Task::factory()->create(['title' => 'Draft Task', 'is_published' => false]);

    $this->actingAs($admin)
        ->get(route('admin.task.index', ['is_published' => 1]))
        ->assertInertia(fn (Assert $page) =>
            $page->where('tasks.data', function ($tasks) {
                $titles = collect($tasks)->pluck('title');

                return $titles->contains('Published Task')
                    && !$titles->contains('Draft Task');
            })
        );
});

// === COMBINATION ===

test('admin can combine filters', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $teacher = User::factory()->create(['name' => 'John']);
    $class = Classroom::factory()->create([
        'teacher_id' => $teacher->id,
        'name' => 'Class A'
    ]);

    Task::factory()->create([
        'title' => 'Essay Final',
        'classroom_id' => $class->id,
        'is_published' => true,
    ]);

    Task::factory()->create([
        'title' => 'Essay Draft',
        'classroom_id' => $class->id,
        'is_published' => false,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.task.index', [
            'search' => 'Final',
            'teacher_id' => $teacher->id,
            'classroom_id' => $class->id,
            'is_published' => 1,
        ]))
        ->assertInertia(fn (Assert $page) =>
            $page->where('tasks.data', fn ($tasks) =>
                count($tasks) === 1 && $tasks[0]['title'] === 'Essay Final'
            )
        );
});

// === INVALID FILTER ===

test('invalid teacher_id returns empty result', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Task::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.task.index', ['teacher_id' => 999]))
        ->assertInertia(fn (Assert $page) =>
            $page->where('tasks.data', fn ($tasks) => count($tasks) === 0)
        );
});

test('invalid classroom_id returns empty result', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Task::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.task.index', ['classroom_id' => 999]))
        ->assertInertia(fn (Assert $page) =>
            $page->where('tasks.data', fn ($tasks) => count($tasks) === 0)
        );
});

// === PAGINATION ===

test('pagination works properly', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Task::factory()->count(15)->create();

    $this->actingAs($admin)
        ->get(route('admin.task.index'))
        ->assertInertia(fn (Assert $page) =>
            $page->has('tasks.data', 10)
        );
});

// === Admin/TaskController.destroy ===

test('admin can delete task without submission', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $task = Task::factory()->create();

    $response = $this->actingAs($admin)
        ->delete(route('admin.task.destroy', $task));

    $response->assertRedirect(route('admin.task.index'));

    $response->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' => 'Task deleted successfully.',
    ]);

    $this->assertDatabaseMissing('tasks', [
        'id' => $task->id
    ]);
});

test('admin cannot delete task with submission', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $task = Task::factory()->create();
    Submission::factory()->create(['task_id' => $task->id]);

    $response = $this->actingAs($admin)
        ->delete(route('admin.task.destroy', $task));

    $response->assertInertiaFlash('toast', [
        'type' => 'error',
        'message' => 'Task cannot be deleted because it has submissions.',
    ]);

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id
    ]);
});

test('non-admin cannot delete task', function () {
    $user = User::factory()->create(['role' => 'teacher']);
    $task = Task::factory()->create();

    $this->actingAs($user)
        ->delete(route('admin.task.destroy', $task))
        ->assertForbidden();
});