<?php

use App\Models\User;
use App\Models\Classroom;
use App\Models\Task;
use App\Models\TaskRubric;
use App\Models\Submission;
use App\Models\File;
use App\Models\TemporaryFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

// === Teacher/TaskController.index ===

test('teacher can view task list card inside a classroom', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);

    Task::factory()
        ->count(3)
        ->create([
            'classroom_id' => $classroom->id,
            'created_by' => $teacher->id,
        ]);

    $this->actingAs($teacher)
        ->get(route('teacher.classroom.task.index', $classroom))
        ->assertSuccessful()
        ->assertInertia(
            fn(Assert $page) => $page
                ->component('teacher/task/Index')
                ->has('tasks.data', 3),
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

// === Teacher/TaskController.create ===
test('teacher can view create task form', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);

    $this->actingAs($teacher)
        ->get(route('teacher.classroom.task.create', $classroom))
        ->assertSuccessful()
        ->assertInertia(fn(Assert $page) => $page->component('teacher/task/Form'));
});

test('teacher cannot view create task form from other classroom teacher', function () {
    $teacher1 = User::factory()->create(['role' => 'teacher']);
    $teacher2 = User::factory()->create(['role' => 'teacher']);

    $classroom2 = Classroom::factory()->create(['teacher_id' => $teacher2->id]);

    $this->actingAs($teacher1)
        ->get(route('teacher.classroom.task.create', $classroom2))
        ->assertForbidden();
});

// === Teacher/TaskController.store ===

test('teacher can create task with complete attributes', function () {
    Storage::fake('public');
    $file1 = UploadedFile::fake()->create('materi-tambahan.pdf', 1000);
    $file2 = UploadedFile::fake()->create('materi-pendukung.pdf', 2000);
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);

    $deadline = Carbon::now()->addDays(7)->toDateTimeString();

    $uploadedFile1 = $this->actingAs($teacher)
        ->postJson(route('file.upload'), [
            'file' => $file1,
        ])
        ->assertSuccessful();

    $uploadedFile2 = $this->actingAs($teacher)
        ->postJson(route('file.upload'), [
            'file' => $file2,
        ])
        ->assertSuccessful();

    $response = $this->actingAs($teacher)->post(
        route('teacher.classroom.task.store', $classroom),
        [
            'title' => 'Complete Task',
            'description' => 'This is a complete description',
            'deadline' => $deadline,
            'is_published' => true,
            'rubrics' => [
                [
                    'title' => 'Grammar',
                    'description' => 'Grammar is good',
                    'max_score' => 25,
                    'order' => 1,
                ],
                [
                    'title' => 'Vocabulary',
                    'description' => 'Vocabulary is good',
                    'max_score' => 25,
                    'order' => 2,
                ],
                [
                    'title' => 'Organization',
                    'description' => 'Organization is good',
                    'max_score' => 25,
                    'order' => 3,
                ],
                [
                    'title' => 'Conventions',
                    'description' => 'Conventions is good',
                    'max_score' => 25,
                    'order' => 4,
                ],
            ],
            'attachments' => [
                $uploadedFile1->json('file')['id'],
                $uploadedFile2->json('file')['id'],
            ],
        ],
    );

    $response->assertRedirect(
        route('teacher.classroom.task.index', $classroom),
    );
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

    $task = Task::where('title', 'Complete Task')->first();

    $this->assertDatabaseCount('task_rubrics', 4);
    $this->assertDatabaseHas('task_rubrics', [
        'task_id' => $task->id,
        'title' => 'Grammar',
        'max_score' => 25,
        'order' => 1,
    ]);

    $this->assertDatabaseCount('files', 2);
    $this->assertDatabaseHas('files', [
        'original_name' => 'materi-tambahan.pdf',
        'fileable_type' => Task::class,
    ]);
    $this->assertDatabaseHas('files', [
        'original_name' => 'materi-pendukung.pdf',
        'fileable_type' => Task::class,
    ]);

    Storage::disk('public')->assertExists(
        'tasks/' . $uploadedFile1->json('file')['filename'],
    );
    Storage::disk('public')->assertExists(
        'tasks/' . $uploadedFile2->json('file')['filename'],
    );
    Storage::disk('public')->assertMissing(
        'tmp/' . $uploadedFile1->json('file')['filename'],
    );
    Storage::disk('public')->assertMissing(
        'tmp/' . $uploadedFile2->json('file')['filename'],
    );
});

test('teacher can create task without a file attachment', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);

    $deadline = Carbon::now()->addDays(7)->toDateTimeString();

    $response = $this->actingAs($teacher)->post(
        route('teacher.classroom.task.store', $classroom),
        [
            'title' => 'Complete Task',
            'description' => 'This is a complete description',
            'deadline' => $deadline,
            'is_published' => true,
            'rubrics' => [
                [
                    'title' => 'Grammar',
                    'description' => 'Grammar is good',
                    'max_score' => 25,
                    'order' => 1,
                ],
                [
                    'title' => 'Vocabulary',
                    'description' => 'Vocabulary is good',
                    'max_score' => 25,
                    'order' => 2,
                ],
                [
                    'title' => 'Organization',
                    'description' => 'Organization is good',
                    'max_score' => 25,
                    'order' => 3,
                ],
                [
                    'title' => 'Conventions',
                    'description' => 'Conventions is good',
                    'max_score' => 25,
                    'order' => 4,
                ],
            ],
        ],
    );

    $response->assertRedirect(
        route('teacher.classroom.task.index', $classroom),
    );
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

    $task = Task::where('title', 'Complete Task')->first();

    $this->assertDatabaseCount('task_rubrics', 4);
    $this->assertDatabaseHas('task_rubrics', [
        'task_id' => $task->id,
        'title' => 'Grammar',
        'max_score' => 25,
        'order' => 1,
    ]);
});

test(
    'teacher cannot create task using invalid or unowned temporary file id',
    function () {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $otherUser = User::factory()->create(['role' => 'teacher']);
        $classroom = Classroom::factory()->create([
            'teacher_id' => $teacher->id,
        ]);

        $unownedFile = TemporaryFile::create([
            'filename' => 'some-file.pdf',
            'original_name' => 'some-file.pdf',
            'uploaded_by' => $otherUser->id,
        ]);

        $response = $this->actingAs($teacher)->post(
            route('teacher.classroom.task.store', $classroom),
            [
                'title' => 'Task Title',
                'deadline' => Carbon::now()->addDays(7)->toDateTimeString(),
                'rubrics' => [
                    [
                        'title' => 'Grammar',
                        'description' => 'Grammar description',
                        'max_score' => 25,
                        'order' => 1,
                    ],
                ],
                'attachments' => ['invalid-id', $unownedFile->id],
            ],
        );

        $response->assertSessionHasErrors(['attachments.0', 'attachments.1']);
    },
);

test(
    'teacher cannot create task with negative or zero rubric max_score',
    function () {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $classroom = Classroom::factory()->create([
            'teacher_id' => $teacher->id,
        ]);

        $response = $this->actingAs($teacher)->post(
            route('teacher.classroom.task.store', $classroom),
            [
                'title' => 'Task Title',
                'deadline' => Carbon::now()->addDays(7)->toDateTimeString(),
                'rubrics' => [
                    [
                        'title' => 'Grammar',
                        'max_score' => 0,
                        'order' => 1,
                    ],
                ],
            ],
        );

        $response->assertSessionHasErrors('rubrics.0.max_score');
    },
);

test('teacher cannot create task with missing rubric title', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);

    $response = $this->actingAs($teacher)->post(
        route('teacher.classroom.task.store', $classroom),
        [
            'title' => 'Task Title',
            'deadline' => Carbon::now()->addDays(7)->toDateTimeString(),
            'rubrics' => [
                [
                    'title' => '', // Kosong
                    'max_score' => 25,
                    'order' => 1,
                ],
            ],
        ],
    );

    $response->assertSessionHasErrors('rubrics.0.title');
});

test('teacher cannot create task with missing rubric description', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);

    $response = $this->actingAs($teacher)->post(
        route('teacher.classroom.task.store', $classroom),
        [
            'title' => 'Task Title',
            'deadline' => Carbon::now()->addDays(7)->toDateTimeString(),
            'rubrics' => [
                [
                    'title' => 'Grammar',
                    'description' => '', // Kosong
                    'max_score' => 25,
                    'order' => 1,
                ],
            ],
        ],
    );

    $response->assertSessionHasErrors('rubrics.0.description');
});

test('teacher cannot create task with missing rubric order', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);

    $response = $this->actingAs($teacher)->post(
        route('teacher.classroom.task.store', $classroom),
        [
            'title' => 'Task Title',
            'deadline' => Carbon::now()->addDays(7)->toDateTimeString(),
            'rubrics' => [
                [
                    'title' => 'Grammar',
                    'description' => 'Grammar is good',
                    'max_score' => 25,
                    // 'order' => 1, // Missing order
                ],
            ],
        ],
    );

    $response->assertSessionHasErrors('rubrics.0.order');
});

test('teacher cannot create task with non-integer rubric order', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);

    $response = $this->actingAs($teacher)->post(
        route('teacher.classroom.task.store', $classroom),
        [
            'title' => 'Task Title',
            'deadline' => Carbon::now()->addDays(7)->toDateTimeString(),
            'rubrics' => [
                [
                    'title' => 'Grammar',
                    'description' => 'Grammar is good',
                    'max_score' => 25,
                    'order' => 'satu', // Non-integer
                ],
            ],
        ],
    );

    $response->assertSessionHasErrors('rubrics.0.order');
});

test('teacher cannot create task with duplicate rubric title', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);

    $response = $this->actingAs($teacher)->post(
        route('teacher.classroom.task.store', $classroom),
        [
            'title' => 'Task Title',
            'deadline' => Carbon::now()->addDays(7)->toDateTimeString(),
            'rubrics' => [
                [
                    'title' => 'Grammar',
                    'description' => 'Grammar is good',
                    'max_score' => 25,
                    'order' => 1,
                ],
                [
                    'title' => 'Grammar', // Duplicate title
                    'description' => 'Grammar is good',
                    'max_score' => 25,
                    'order' => 2,
                ],
            ],
        ],
    );

    $response->assertSessionHasErrors('rubrics.1.title');
});

test('teacher cannot create task with duplicate rubric order', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);

    $response = $this->actingAs($teacher)->post(
        route('teacher.classroom.task.store', $classroom),
        [
            'title' => 'Task Title',
            'deadline' => Carbon::now()->addDays(7)->toDateTimeString(),
            'rubrics' => [
                [
                    'title' => 'Grammar',
                    'description' => 'Grammar is good',
                    'max_score' => 25,
                    'order' => 1,
                ],
                [
                    'title' => 'Vocabulary',
                    'description' => 'Vocabulary is good',
                    'max_score' => 25,
                    'order' => 1, // Duplicate order
                ],
            ],
        ],
    );

    $response->assertSessionHasErrors('rubrics.1.order');
});

test('teacher cannot create task without rubric', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);

    $deadline = Carbon::now()->addDays(7)->toDateTimeString();

    $response = $this->actingAs($teacher)->post(
        route('teacher.classroom.task.store', $classroom),
        [
            'title' => 'Complete Task',
            'description' => 'This is a complete description',
            'deadline' => $deadline,
            'is_published' => true,
        ],
    );

    $response->assertSessionHasErrors('rubrics');
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

// === Teacher/TaskController.edit ===

test('teacher can view edit task form', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $task = Task::factory()->create([
        'classroom_id' => $classroom->id,
        'created_by' => $teacher->id,
        'is_published' => false,
    ]);

    TaskRubric::factory(5)->create([
        'task_id' => $task->id,
    ]);

    $task->files()->createMany([
        [
            'original_name' => 'file1.txt',
            'filename' => 'file1.txt',
            'path' => 'tasks/file1.txt',
            'uploaded_by' => $teacher->id,
        ],
        [
            'original_name' => 'file2.txt',
            'filename' => 'file2.txt',
            'path' => 'tasks/file2.txt',
            'uploaded_by' => $teacher->id,
        ],
    ]);

    $this->actingAs($teacher)
        ->get(route('teacher.classroom.task.edit', [$classroom, $task]))
        ->assertSuccessful()
        ->assertInertia(fn(Assert $page) => $page->component('teacher/task/Form'))
        ->assertInertia(function (Assert $page) use ($task, $classroom) {
            $page->has('task', function (Assert $page) use ($task, $classroom) {
                $page->where('id', $task->id)
                    ->where('classroom_id', $classroom->id)
                    ->has('classroom')
                    ->has('files', 2)
                    ->etc();
            })
            ->has('task.rubrics', 5);
        });
});

test('teacher cannot view edit task form for other classroom teacher', function () {
    $teacher1 = User::factory()->create(['role' => 'teacher']);
    $teacher2 = User::factory()->create(['role' => 'teacher']);
    $classroom2 = Classroom::factory()->create(['teacher_id' => $teacher2->id]);
    $task2 = Task::factory()->create([
        'classroom_id' => $classroom2->id,
        'created_by' => $teacher2->id,
    ]);

    $this->actingAs($teacher1)
        ->get(route('teacher.classroom.task.edit', [$classroom2, $task2]))
        ->assertForbidden();
});

test('teacher cannot view edit task form if task is published', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $task = Task::factory()->create([
        'classroom_id' => $classroom->id,
        'created_by' => $teacher->id,
        'is_published' => true,
    ]);

    $response = $this->actingAs($teacher)->get(
        route('teacher.classroom.task.edit', [$classroom, $task]),
    );

    $response->assertRedirect(route('teacher.classroom.task.index', $classroom));
    $response->assertInertiaFlash('toast', [
        'type' => 'error',
        'message' => 'You cannot edit a published task!',
    ]);
});


// === Teacher/TaskController.update ===

test('teacher can update task attributes while still a draft', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $task = Task::factory()->create([
        'classroom_id' => $classroom->id,
        'created_by' => $teacher->id,
        'is_published' => false,
    ]);
    TaskRubric::factory()->create(['task_id' => $task->id]);

    $newDeadline = Carbon::now()->addDays(10)->toDateTimeString();

    $response = $this->actingAs($teacher)->put(
        route('teacher.classroom.task.update', [$classroom, $task]),
        [
            'title' => 'Updated Task Title',
            'description' => 'Updated description',
            'deadline' => $newDeadline,
            'is_published' => true,
            'rubrics' => [
                [
                    'title' => 'Grammar',
                    'description' => 'Grammar is good',
                    'max_score' => 25,
                    'order' => 1,
                ],
                [
                    'title' => 'Vocabulary',
                    'description' => 'Vocabulary is good',
                    'max_score' => 25,
                    'order' => 2,
                ],
                [
                    'title' => 'Organization',
                    'description' => 'Organization is good',
                    'max_score' => 25,
                    'order' => 3,
                ],
                [
                    'title' => 'Conventions',
                    'description' => 'Conventions is good',
                    'max_score' => 25,
                    'order' => 4,
                ],
            ],
        ],
    );

    $response->assertRedirect(route('teacher.classroom.task.index', $classroom));
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

    $this->assertDatabaseCount('task_rubrics', 4);
    $this->assertDatabaseHas('task_rubrics', [
        'task_id' => $task->id,
        'title' => 'Grammar',
        'max_score' => 25,
        'order' => 1,
    ]);
});

test('teacher can remove a rubric during task update', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $task = Task::factory()->create([
        'classroom_id' => $classroom->id,
        'created_by' => $teacher->id,
        'is_published' => false,
    ]);

    // Asumsi task ini awalnya punya 2 rubrik
    TaskRubric::factory()->create([
        'task_id' => $task->id,
        'title' => 'Grammar',
        'order' => 1,
    ]);
    TaskRubric::factory()->create([
        'task_id' => $task->id,
        'title' => 'Vocabulary',
        'order' => 2,
    ]);

    $this->assertDatabaseCount('task_rubrics', 2);

    // Saat update, teacher HANYA mengirimkan 1 rubrik (Vocabulary dihapus)
    $response = $this->actingAs($teacher)->put(
        route('teacher.classroom.task.update', [$classroom, $task]),
        [
            'title' => 'Updated Task',
            'deadline' => Carbon::now()->addDays(10)->toDateTimeString(),
            'is_published' => false,
            'rubrics' => [
                [
                    'title' => 'Grammar',
                    'description' => 'Grammar description',
                    'max_score' => 25,
                    'order' => 1,
                ],
            ],
        ],
    );

    $response->assertRedirect(route('teacher.classroom.task.index', $classroom));

    $response->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' => 'Task updated successfully!',
    ]);

    // Pastikan di database rubriknya tinggal 1 (proses sinkronisasi berhasil membuang Vocabulary)
    $this->assertDatabaseCount('task_rubrics', 1);
    $this->assertDatabaseMissing('task_rubrics', [
        'title' => 'Vocabulary',
    ]);
});

test('teacher can remove attachment during task update', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $task = Task::factory()->create([
        'classroom_id' => $classroom->id,
        'created_by' => $teacher->id,
        'is_published' => false,
    ]);

    Storage::fake('public');

    // Create two files attached to the task
    $f1 = File::factory()->create([
        'fileable_id' => $task->id,
        'fileable_type' => Task::class,
        'original_name' => 'keep.jpg',
        'path' => 'tasks/keep.jpg',
        'uploaded_by' => $teacher->id,
    ]);
    $f2 = File::factory()->create([
        'fileable_id' => $task->id,
        'fileable_type' => Task::class,
        'original_name' => 'remove.jpg',
        'path' => 'tasks/remove.jpg',
        'uploaded_by' => $teacher->id,
    ]);

    // Put files in fake storage
    Storage::disk('public')->put($f1->path, 'keep me');
    Storage::disk('public')->put($f2->path, 'remove me');

    $this->assertDatabaseCount('files', 2);
    Storage::disk('public')->assertExists($f1->path);
    Storage::disk('public')->assertExists($f2->path);

    // Update task, keeping only $f1
    $response = $this->actingAs($teacher)->put(
        route('teacher.classroom.task.update', [$classroom, $task]),
        [
            'title' => 'Updated Task',
            'deadline' => Carbon::now()->addDays(10)->toDateTimeString(),
            'is_published' => false,
            'rubrics' => [
                [
                    'title' => 'Grammar',
                    'description' => 'Grammar description',
                    'max_score' => 25,
                    'order' => 1,
                ],
            ],
            'attachments' => [$f1->id],
        ],
    );

    $response->assertRedirect(route('teacher.classroom.task.index', $classroom));

    $response->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' => 'Task updated successfully!',
    ]);

    // Assert database
    $this->assertDatabaseCount('files', 1);
    $this->assertDatabaseHas('files', ['id' => $f1->id]);
    $this->assertDatabaseMissing('files', ['id' => $f2->id]);

    // Assert physical storage
    Storage::disk('public')->assertExists($f1->path);
    Storage::disk('public')->assertMissing($f2->path);
});

test('teacher cannot update publised task', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $task = Task::factory()->create([
        'classroom_id' => $classroom->id,
        'created_by' => $teacher->id,
        'is_published' => true,
    ]);
    TaskRubric::factory()->create(['task_id' => $task->id, 'max_score' => 25]);

    $response = $this->actingAs($teacher)->put(
        route('teacher.classroom.task.update', [$classroom, $task]),
        [
            'title' => 'Updated Task Title',
            'description' => 'Updated description',
            'deadline' => Carbon::now()->addDays(10)->toDateTimeString(),
            'is_published' => true,
            'rubrics' => [
                [
                    'title' => 'Grammar',
                    'description' => 'Grammar is good',
                    'max_score' => 25,
                    'order' => 1,
                ],
                [
                    'title' => 'Vocabulary',
                    'description' => 'Vocabulary is good',
                    'max_score' => 25,
                    'order' => 2,
                ],
                [
                    'title' => 'Organization',
                    'description' => 'Organization is good',
                    'max_score' => 25,
                    'order' => 3,
                ],
                [
                    'title' => 'Conventions',
                    'description' => 'Conventions is good',
                    'max_score' => 25,
                    'order' => 4,
                ],
            ],
        ],
    );

    $response->assertRedirect(route('teacher.classroom.task.index', $classroom));
    $response->assertInertiaFlash('toast', [
        'type' => 'error',
        'message' => 'You cannot update a published task!',
    ]);
});

test('teacher cannot update task without title', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $task = Task::factory()->create([
        'classroom_id' => $classroom->id,
        'created_by' => $teacher->id,
    ]);

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
    $task = Task::factory()->create([
        'classroom_id' => $classroom->id,
        'created_by' => $teacher->id,
    ]);

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
    $task2 = Task::factory()->create([
        'classroom_id' => $classroom2->id,
        'created_by' => $teacher2->id,
    ]);

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
    $task = Task::factory()->create([
        'classroom_id' => $classroom->id,
        'created_by' => $teacher->id,
    ]);

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
    $task = Task::factory()->create([
        'classroom_id' => $classroom->id,
        'created_by' => $teacher->id,
    ]);

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
    $task2 = Task::factory()->create([
        'classroom_id' => $classroom2->id,
        'created_by' => $teacher2->id,
    ]);

    $this->actingAs($teacher1)
        ->put(route('teacher.classroom.task.update', [$classroom2, $task2]), [
            'title' => 'Updated Task Title',
            'description' => 'Updated description',
            'deadline' => Carbon::now()->addDays(3)->toDateTimeString(),
        ])
        ->assertForbidden();
});

// === Teacher/TaskController.destroy ===

test('teacher can delete unpublished task', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $task = Task::factory()->create([
        'classroom_id' => $classroom->id,
        'created_by' => $teacher->id,
        'is_published' => false,
    ]);

    $response = $this->actingAs($teacher)->delete(
        route('teacher.classroom.task.destroy', [$classroom, $task]),
    );

    $response->assertRedirect(route('teacher.classroom.task.index', $classroom));
    $response->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' => 'Task deleted successfully!',
    ]);

    $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
});

test('teacher cannot delete published task', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $task = Task::factory()->create([
        'classroom_id' => $classroom->id,
        'created_by' => $teacher->id,
        'is_published' => true,
    ]);

    $response = $this->actingAs($teacher)->delete(
        route('teacher.classroom.task.destroy', [$classroom, $task]),
    );

    $response->assertRedirect(route('teacher.classroom.task.index', $classroom));
    $response->assertInertiaFlash('toast', [
        'type' => 'error',
        'message' => 'You cannot delete a published task!',
    ]);

    $this->assertDatabaseHas('tasks', ['id' => $task->id]);
});

test('teacher cannot delete other teacher task', function () {
    $teacher1 = User::factory()->create(['role' => 'teacher']);
    $teacher2 = User::factory()->create(['role' => 'teacher']);
    $classroom2 = Classroom::factory()->create(['teacher_id' => $teacher2->id]);
    $task2 = Task::factory()->create([
        'classroom_id' => $classroom2->id,
        'created_by' => $teacher2->id,
    ]);

    $this->actingAs($teacher1)
        ->delete(route('teacher.classroom.task.destroy', [$classroom2, $task2]))
        ->assertForbidden();
});

test('teacher cannot delete task that already has submissions', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $task = Task::factory()->create([
        'classroom_id' => $classroom->id,
        'created_by' => $teacher->id,
        'is_published' => false,
    ]);

    // Asumsi ada submission
    Submission::factory()->create(['task_id' => $task->id]);

    $response = $this->actingAs($teacher)->delete(
        route('teacher.classroom.task.destroy', [$classroom, $task]),
    );

    $response->assertRedirect(route('teacher.classroom.task.index', $classroom));
    $response->assertInertiaFlash('toast', [
        'type' => 'error',
        'message' => 'Cannot delete task that has submissions!',
    ]);

    $this->assertDatabaseHas('tasks', ['id' => $task->id]);
});

// === Teacher/TaskController.publish ===

test('teacher can publish a created task', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $task = Task::factory()->create([
        'classroom_id' => $classroom->id,
        'created_by' => $teacher->id,
        'is_published' => false,
    ]);

    $response = $this->actingAs($teacher)->patch(
        route('teacher.classroom.task.publish', [$classroom, $task]),
    );

    $response->assertRedirect(route('teacher.classroom.task.index', $classroom));
    $response->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' => 'Task published successfully!',
    ]);

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'is_published' => true,
    ]);
});

test('teacher can unpublish a published task if the task has no submissions', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $task = Task::factory()->create([
        'classroom_id' => $classroom->id,
        'created_by' => $teacher->id,
        'is_published' => true,
    ]);

    $response = $this->actingAs($teacher)->patch(
        route('teacher.classroom.task.unpublish', [$classroom, $task]),
    );

    $response->assertRedirect(route('teacher.classroom.task.index', $classroom));
    $response->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' => 'Task unpublished successfully!',
    ]);

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'is_published' => false,
    ]);
});

test('teacher cannot unpublish a published task if the task has submissions', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $student = User::factory()->create(['role' => 'student']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $task = Task::factory()->create([
        'classroom_id' => $classroom->id,
        'created_by' => $teacher->id,
        'is_published' => true,
    ]);
    Submission::factory()->create([
        'task_id' => $task->id,
        'user_id' => $student->id,
    ]);

    $response = $this->actingAs($teacher)->patch(
        route('teacher.classroom.task.unpublish', [$classroom, $task]),
    );

    $response->assertRedirect(route('teacher.classroom.task.index', $classroom));
    $response->assertInertiaFlash('toast', [
        'type' => 'error',
        'message' => 'Cannot unpublish task that has submissions!',
    ]);

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'is_published' => true,
    ]);
});

test('teacher cannot publish other teacher\'s classroom task', function () {
    $teacher1 = User::factory()->create(['role' => 'teacher']);
    $teacher2 = User::factory()->create(['role' => 'teacher']);
    $classroom2 = Classroom::factory()->create(['teacher_id' => $teacher2->id]);
    $task2 = Task::factory()->create([
        'classroom_id' => $classroom2->id,
        'created_by' => $teacher2->id,
    ]);

    $this->actingAs($teacher1)->patch(route('teacher.classroom.task.publish', [$classroom2, $task2]))->assertForbidden();
});

test('teacher cannot unpublish other teacher\'s classroom task', function () {
    $teacher1 = User::factory()->create(['role' => 'teacher']);
    $teacher2 = User::factory()->create(['role' => 'teacher']);
    $classroom2 = Classroom::factory()->create(['teacher_id' => $teacher2->id]);
    $task2 = Task::factory()->create([
        'classroom_id' => $classroom2->id,
        'created_by' => $teacher2->id,
    ]);

    $this->actingAs($teacher1)->patch(route('teacher.classroom.task.unpublish', [$classroom2, $task2]))->assertForbidden();
});

// === ROUTE ACCESS ===

test('non-teacher cannot access teacher task routes', function () {
    $student = User::factory()->create(['role' => 'student']);
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $task = Task::factory()->create([
        'classroom_id' => $classroom->id,
        'created_by' => $teacher->id,
    ]);

    $this->actingAs($student)
        ->post(route('teacher.classroom.task.store', $classroom), [])
        ->assertForbidden();

    $this->actingAs($student)
        ->put(route('teacher.classroom.task.update', [$classroom, $task]), [])
        ->assertForbidden();

    $this->actingAs($student)
        ->delete(route('teacher.classroom.task.destroy', [$classroom, $task]))
        ->assertForbidden();
});

test('guest cannot access teacher task routes', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);

    $this->get(
        route('teacher.classroom.task.index', $classroom),
    )->assertRedirect('/login');
});
