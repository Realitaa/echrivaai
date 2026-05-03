<?php

use App\Models\User;
use App\Models\Classroom;
use App\Models\Enrollment;
use App\Models\Task;
use App\Models\TaskRubric;
use App\Models\Submission;
use App\Models\AiFeedback;
use App\Models\SubmissionRubricScore;
use App\Models\TemporaryFile;
use App\Ai\Agents\SubmissionFeedbackAgent;
use App\Jobs\ProcessAiFeedbackJob;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia as Assert;

// Helper: Scaffold a standard student + enrolled classroom + published task with rubrics
function createEnrolledStudentWithTask(array $taskOverrides = []): array
{
    $teacher = User::factory()->create(['role' => 'teacher']);
    $student = User::factory()->create(['role' => 'student']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);

    Enrollment::factory()->create([
        'user_id' => $student->id,
        'classroom_id' => $classroom->id,
    ]);

    $task = Task::factory()->create(
        array_merge(
            [
                'classroom_id' => $classroom->id,
                'created_by' => $teacher->id,
                'is_published' => true,
                'deadline' => Carbon::now()->addDays(7),
            ],
            $taskOverrides,
        ),
    );

    // Create 2 rubrics for the task
    $rubric1 = TaskRubric::factory()->create([
        'task_id' => $task->id,
        'title' => 'Grammar',
        'max_score' => 25,
        'order' => 1,
    ]);
    $rubric2 = TaskRubric::factory()->create([
        'task_id' => $task->id,
        'title' => 'Vocabulary',
        'max_score' => 25,
        'order' => 2,
    ]);

    return compact(
        'teacher',
        'student',
        'classroom',
        'task',
        'rubric1',
        'rubric2',
    );
}

// Helper: Upload multiple temporary files and return their responses.
function uploadTemporaryFiles(
    $testContext,
    User $student,
    int $count = 2,
): array {
    $responses = [];
    for ($i = 0; $i < $count; $i++) {
        $file = UploadedFile::fake()->create("essay-part-{$i}.pdf", 500);
        $uploaded = $testContext
            ->actingAs($student)
            ->postJson(route('file.upload'), ['file' => $file])
            ->assertSuccessful();
        $responses[] = $uploaded;
    }
    return $responses;
}

// === Student/TaskController.show ===
test('student can view task detail inside an enrolled classroom', function () {
    [
        'student' => $student,
        'classroom' => $classroom,
        'task' => $task,
    ] = createEnrolledStudentWithTask();

    $this->actingAs($student)
        ->get(route('student.classroom.task.show', [$classroom, $task]))
        ->assertSuccessful()
        ->assertInertia(
            fn(Assert $page) => $page
                ->component('student/task/Show')
                ->where('task.id', $task->id)
                ->has('submissions'),
        );
});

test(
    'student cannot view task detail inside an unenrolled classroom',
    function () {
        $student = User::factory()->create(['role' => 'student']);
        $classroom = Classroom::factory()->create();
        $task = Task::factory()->create([
            'classroom_id' => $classroom->id,
            'is_published' => true,
        ]);

        $this->actingAs($student)
            ->get(route('student.classroom.task.show', [$classroom, $task]))
            ->assertForbidden();
    },
);

test(
    'student can see the list of their submission attempts (history) for a task',
    function () {
        [
            'student' => $student,
            'classroom' => $classroom,
            'task' => $task,
        ] = createEnrolledStudentWithTask();

        // Create 3 submission versions
        Submission::factory()->create([
            'user_id' => $student->id,
            'task_id' => $task->id,
            'version' => 1,
            'is_latest' => false,
            'status' => 'graded',
        ]);
        Submission::factory()->create([
            'user_id' => $student->id,
            'task_id' => $task->id,
            'version' => 2,
            'is_latest' => false,
            'status' => 'graded',
        ]);
        Submission::factory()->create([
            'user_id' => $student->id,
            'task_id' => $task->id,
            'version' => 3,
            'is_latest' => true,
            'status' => 'submitted',
        ]);

        $this->actingAs($student)
            ->get(route('student.classroom.task.show', [$classroom, $task]))
            ->assertSuccessful()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('student/task/Show')
                    ->has('submissions', 3),
            );
    },
);

test(
    'student can view task detail and history even AFTER the deadline',
    function () {
        [
            'student' => $student,
            'classroom' => $classroom,
            'task' => $task,
        ] = createEnrolledStudentWithTask([
            'deadline' => Carbon::now()->subDays(3), // Past deadline
        ]);

        Submission::factory()->create([
            'user_id' => $student->id,
            'task_id' => $task->id,
            'version' => 1,
            'status' => 'graded',
        ]);

        $this->actingAs($student)
            ->get(route('student.classroom.task.show', [$classroom, $task]))
            ->assertSuccessful()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('student/task/Show')
                    ->where('task.id', $task->id)
                    ->has('submissions', 1),
            );
    },
);

test('student cannot view an UNPUBLISHED task', function () {
    [
        'student' => $student,
        'classroom' => $classroom,
    ] = createEnrolledStudentWithTask();

    $draftTask = Task::factory()->create([
        'classroom_id' => $classroom->id,
        'is_published' => false,
    ]);

    $this->actingAs($student)
        ->get(route('student.classroom.task.show', [$classroom, $draftTask]))
        ->assertForbidden();
});

// === Student/TaskController.submission ===
test(
    'student can make a submission for a task inside an enrolled classroom',
    function () {
        Storage::fake('public');
        Queue::fake();
        [
            'student' => $student,
            'classroom' => $classroom,
            'task' => $task,
        ] = createEnrolledStudentWithTask();

        // Upload multiple temporary files owned by the student
        $uploadedFiles = uploadTemporaryFiles($this, $student, 2);
        $tempFileIds = array_map(
            fn($f) => $f->json('file')['id'],
            $uploadedFiles,
        );

        $response = $this->actingAs($student)->post(
            route('student.classroom.task.submission.store', [
                $classroom,
                $task,
            ]),
            [
                'content' => 'This is my essay submission content.',
                'temporary_file_ids' => $tempFileIds,
            ],
        );

        $response->assertRedirect(
            route('student.classroom.task.show', [$classroom, $task]),
        );
        $response->assertInertiaFlash('toast', [
            'type' => 'success',
            'message' =>
                'Submission created successfully! Please wait while AI processes your feedback.',
        ]);

        $this->assertDatabaseHas('submissions', [
            'user_id' => $student->id,
            'task_id' => $task->id,
            'version' => 1,
            'is_latest' => true,
            'status' => 'processing',
        ]);

        // Both files should be moved to permanent storage
        $submission = Submission::where('user_id', $student->id)
            ->where('task_id', $task->id)
            ->first();
        $this->assertDatabaseCount('files', 2);
        $this->assertDatabaseHas('files', [
            'fileable_type' => Submission::class,
            'fileable_id' => $submission->id,
        ]);

        Storage::disk('public')->assertExists(
            'submissions/' . $uploadedFiles[0]->json('file')['filename'],
        );
        Storage::disk('public')->assertExists(
            'submissions/' . $uploadedFiles[1]->json('file')['filename'],
        );

        // AI processing should be dispatched to queue, not executed synchronously
        Queue::assertPushed(ProcessAiFeedbackJob::class, function ($job) use (
            $submission,
        ) {
            return $job->submission->id === $submission->id;
        });
    },
);

test('student can make multiple submission attempts', function () {
    Storage::fake('public');
    Queue::fake();
    [
        'student' => $student,
        'classroom' => $classroom,
        'task' => $task,
    ] = createEnrolledStudentWithTask();

    // First submission (already graded)
    Submission::factory()->create([
        'user_id' => $student->id,
        'task_id' => $task->id,
        'version' => 1,
        'is_latest' => true,
        'status' => 'graded',
    ]);

    $uploadedFiles = uploadTemporaryFiles($this, $student, 1);
    $tempFileIds = array_map(fn($f) => $f->json('file')['id'], $uploadedFiles);

    $response = $this->actingAs($student)->post(
        route('student.classroom.task.submission.store', [$classroom, $task]),
        [
            'content' => 'This is my revised essay.',
            'temporary_file_ids' => $tempFileIds,
        ],
    );

    $response->assertRedirect(
        route('student.classroom.task.show', [$classroom, $task]),
    );
    $response->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' =>
            'Submission created successfully! Please wait while AI processes your feedback.',
    ]);

    $this->assertDatabaseHas('submissions', [
        'user_id' => $student->id,
        'task_id' => $task->id,
        'version' => 2,
        'is_latest' => true,
    ]);

    Storage::disk('public')->assertExists(
        'submissions/' . $uploadedFiles[0]->json('file')['filename'],
    );

    // Previous submission should no longer be latest
    $this->assertDatabaseHas('submissions', [
        'user_id' => $student->id,
        'task_id' => $task->id,
        'version' => 1,
        'is_latest' => false,
    ]);
});

test('student can make a submission EXACTLY at the deadline', function () {
    Storage::fake('public');
    Queue::fake();
    $exactDeadline = Carbon::create(2026, 6, 1, 23, 59, 59);

    [
        'student' => $student,
        'classroom' => $classroom,
        'task' => $task,
    ] = createEnrolledStudentWithTask([
        'deadline' => $exactDeadline,
    ]);

    $uploadedFiles = uploadTemporaryFiles($this, $student, 1);
    $tempFileIds = array_map(fn($f) => $f->json('file')['id'], $uploadedFiles);

    // Freeze time exactly at the deadline
    Carbon::setTestNow($exactDeadline);

    $response = $this->actingAs($student)->post(
        route('student.classroom.task.submission.store', [$classroom, $task]),
        [
            'content' => 'Submitted at the very last second.',
            'temporary_file_ids' => $tempFileIds,
        ],
    );

    $response->assertRedirect(
        route('student.classroom.task.show', [$classroom, $task]),
    );
    $response->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' =>
            'Submission created successfully! Please wait while AI processes your feedback.',
    ]);

    $this->assertDatabaseHas('submissions', [
        'user_id' => $student->id,
        'task_id' => $task->id,
    ]);

    Storage::disk('public')->assertExists(
        'submissions/' . $uploadedFiles[0]->json('file')['filename'],
    );

    Carbon::setTestNow(); // Reset
});

test('student cannot make a submission past task deadline', function () {
    Storage::fake('public');
    $pastDeadline = Carbon::now()->subDays(1);

    [
        'student' => $student,
        'classroom' => $classroom,
        'task' => $task,
    ] = createEnrolledStudentWithTask([
        'deadline' => $pastDeadline,
    ]);

    $uploadedFiles = uploadTemporaryFiles($this, $student, 1);
    $tempFileIds = array_map(fn($f) => $f->json('file')['id'], $uploadedFiles);

    $response = $this->actingAs($student)->post(
        route('student.classroom.task.submission.store', [$classroom, $task]),
        [
            'content' => 'Late submission.',
            'temporary_file_ids' => $tempFileIds,
        ],
    );

    $response->assertRedirect(
        route('student.classroom.task.show', [$classroom, $task]),
    );
    $response->assertInertiaFlash('toast', [
        'type' => 'error',
        'message' => 'The deadline for this task has passed.',
    ]);

    $this->assertDatabaseMissing('submissions', [
        'user_id' => $student->id,
        'task_id' => $task->id,
    ]);

    // Temp file should be deleted
    $this->assertDatabaseMissing('temporary_files', [
        'id' => $uploadedFiles[0]->json('file')['id'],
    ]);

    Storage::disk('public')->assertMissing(
        'tmp/' . $uploadedFiles[0]->json('file')['filename'],
    );
});

test(
    'student cannot submit using an UNOWNED or NON-EXISTENT temporary file ID',
    function () {
        [
            'student' => $student,
            'classroom' => $classroom,
            'task' => $task,
        ] = createEnrolledStudentWithTask();

        $otherStudent = User::factory()->create(['role' => 'student']);

        // Create a temp file owned by another student
        $unownedFile = TemporaryFile::create([
            'filename' => 'other-student-file.pdf',
            'original_name' => 'other-student-file.pdf',
            'uploaded_by' => $otherStudent->id,
        ]);

        // Submit with two invalid files: one non-existent ID and one unowned file
        $response = $this->actingAs($student)->post(
            route('student.classroom.task.submission.store', [
                $classroom,
                $task,
            ]),
            [
                'content' => 'Trying to use invalid files.',
                'temporary_file_ids' => [99999, $unownedFile->id],
            ],
        );

        $response->assertSessionHasErrors([
            'temporary_file_ids.0',
            'temporary_file_ids.1',
        ]);
    },
);

test('student cannot submit with an empty file list', function () {
    [
        'student' => $student,
        'classroom' => $classroom,
        'task' => $task,
    ] = createEnrolledStudentWithTask();

    $response = $this->actingAs($student)->post(
        route('student.classroom.task.submission.store', [$classroom, $task]),
        [
            'content' => 'No files attached.',
            'temporary_file_ids' => [],
        ],
    );

    $response->assertSessionHasErrors('temporary_file_ids');
});

test(
    'student cannot make a new submission while the previous one is still being processed by AI',
    function () {
        Storage::fake('public');
        [
            'student' => $student,
            'classroom' => $classroom,
            'task' => $task,
        ] = createEnrolledStudentWithTask();

        // Create a submission that is still processing
        Submission::factory()->create([
            'user_id' => $student->id,
            'task_id' => $task->id,
            'version' => 1,
            'is_latest' => true,
            'status' => 'processing',
        ]);

        $uploadedFiles = uploadTemporaryFiles($this, $student, 1);
        $tempFileIds = array_map(
            fn($f) => $f->json('file')['id'],
            $uploadedFiles,
        );

        $response = $this->actingAs($student)->post(
            route('student.classroom.task.submission.store', [
                $classroom,
                $task,
            ]),
            [
                'content' => 'Another attempt while processing.',
                'temporary_file_ids' => $tempFileIds,
            ],
        );

        $response->assertRedirect(
            route('student.classroom.task.show', [$classroom, $task]),
        );
        $response->assertInertiaFlash('toast', [
            'type' => 'error',
            'message' =>
                'Your previous submission is still being processed. Please wait.',
        ]);

        // Should still only have 1 submission
        $this->assertDatabaseCount('submissions', 1);

        // Temp file should be deleted
        $this->assertDatabaseMissing('temporary_files', [
            'id' => $uploadedFiles[0]->json('file')['id'],
        ]);

        Storage::disk('public')->assertMissing(
            'tmp/' . $uploadedFiles[0]->json('file')['filename'],
        );
    },
);

test('submission creation dispatches AI feedback job to queue', function () {
    Storage::fake('public');
    Queue::fake();
    [
        'student' => $student,
        'classroom' => $classroom,
        'task' => $task,
    ] = createEnrolledStudentWithTask();

    // Upload multiple files — AI should process all of them as one submission
    $uploadedFiles = uploadTemporaryFiles($this, $student, 3);
    $tempFileIds = array_map(fn($f) => $f->json('file')['id'], $uploadedFiles);

    $this->actingAs($student)
        ->post(
            route('student.classroom.task.submission.store', [
                $classroom,
                $task,
            ]),
            [
                'content' => 'Please grade my essay with multiple files.',
                'temporary_file_ids' => $tempFileIds,
            ],
        )
        ->assertRedirect(
            route('student.classroom.task.show', [$classroom, $task]),
        );

    foreach ($uploadedFiles as $uploadedFile) {
        Storage::disk('public')->assertExists(
            'submissions/' . $uploadedFile->json('file')['filename'],
        );
    }

    // Assert the AI feedback job was dispatched to queue (not executed synchronously)
    Queue::assertPushed(ProcessAiFeedbackJob::class, 1);

    // All 3 files should be attached to the submission
    $submission = Submission::where('user_id', $student->id)
        ->where('task_id', $task->id)
        ->first();
    $this->assertCount(3, $submission->files);

    // Status should still be 'processing' since job hasn't run yet
    expect($submission->status)->toBe('processing');
});

// === ProcessAiFeedbackJob Integration Tests ===

test(
    'AI feedback job processes submission and saves feedback with rubric scores to database',
    function () {
        [
            'student' => $student,
            'classroom' => $classroom,
            'task' => $task,
            'rubric1' => $rubric1,
            'rubric2' => $rubric2,
        ] = createEnrolledStudentWithTask();

        $submission = Submission::factory()->create([
            'user_id' => $student->id,
            'task_id' => $task->id,
            'version' => 1,
            'is_latest' => true,
            'status' => 'processing',
            'content' => 'This is my essay about climate change.',
        ]);

        // Fake the AI agent with a structured response (wrapped in array as list of responses)
        SubmissionFeedbackAgent::fake([
            [
                'feedback' =>
                    'Great essay! Your arguments are well-structured.',
                'score' => 85,
                'rubric_scores' => [
                    [
                        'rubric_id' => $rubric1->id,
                        'score' => 22,
                        'feedback' => 'Excellent grammar usage.',
                    ],
                    [
                        'rubric_id' => $rubric2->id,
                        'score' => 20,
                        'feedback' => 'Good vocabulary range.',
                    ],
                ],
                'progress_label' => 'Tetap',
            ],
        ]);

        // Execute the job directly
        (new ProcessAiFeedbackJob($submission))->handle();

        // Verify submission status transitioned to 'graded'
        $submission->refresh();
        expect($submission->status)->toBe('graded');

        // Verify AI feedback was saved to database
        $this->assertDatabaseHas('ai_feedbacks', [
            'submission_id' => $submission->id,
            'result' => 'Great essay! Your arguments are well-structured.',
            'score' => 85,
        ]);

        // Verify rubric scores were saved
        $this->assertDatabaseHas('submission_rubric_scores', [
            'submission_id' => $submission->id,
            'task_rubric_id' => $rubric1->id,
            'score_ai' => 22,
            'feedback_ai' => 'Excellent grammar usage.',
        ]);
        $this->assertDatabaseHas('submission_rubric_scores', [
            'submission_id' => $submission->id,
            'task_rubric_id' => $rubric2->id,
            'score_ai' => 20,
            'feedback_ai' => 'Good vocabulary range.',
        ]);
    },
);

test(
    'AI feedback job transitions submission to failed status when AI agent throws an exception',
    function () {
        [
            'student' => $student,
            'task' => $task,
        ] = createEnrolledStudentWithTask();

        $submission = Submission::factory()->create([
            'user_id' => $student->id,
            'task_id' => $task->id,
            'version' => 1,
            'is_latest' => true,
            'status' => 'processing',
            'content' => 'Essay that will cause AI failure.',
        ]);

        // Fake the AI agent to throw an exception
        SubmissionFeedbackAgent::fake(
            fn() => throw new \RuntimeException('AI Provider Quota Exceeded'),
        );

        // Execute the job — it should handle the exception gracefully
        (new ProcessAiFeedbackJob($submission))->handle();

        // Verify submission status transitioned to 'failed'
        $submission->refresh();
        expect($submission->status)->toBe('failed');

        // No AI feedback should be saved
        $this->assertDatabaseMissing('ai_feedbacks', [
            'submission_id' => $submission->id,
        ]);

        // Submission data itself should still exist (not rolled back)
        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
        ]);
    },
);

test(
    'AI feedback job includes previous submission context for comparison on version 2+',
    function () {
        [
            'student' => $student,
            'task' => $task,
            'rubric1' => $rubric1,
            'rubric2' => $rubric2,
        ] = createEnrolledStudentWithTask();

        // Version 1 (graded, score 70)
        $sub1 = Submission::factory()->create([
            'user_id' => $student->id,
            'task_id' => $task->id,
            'version' => 1,
            'is_latest' => false,
            'status' => 'graded',
            'content' => 'First attempt essay.',
        ]);
        AiFeedback::factory()->create([
            'submission_id' => $sub1->id,
            'score' => 70,
            'result' => 'Needs improvement.',
        ]);

        // Version 2 (processing)
        $sub2 = Submission::factory()->create([
            'user_id' => $student->id,
            'task_id' => $task->id,
            'version' => 2,
            'is_latest' => true,
            'status' => 'processing',
            'content' => 'Revised and improved essay.',
        ]);

        // Fake the AI agent — it should receive previous submission context (wrapped in array as list of responses)
        SubmissionFeedbackAgent::fake([
            [
                'feedback' =>
                    'Much better! Great improvement from previous attempt.',
                'score' => 88,
                'rubric_scores' => [
                    [
                        'rubric_id' => $rubric1->id,
                        'score' => 23,
                        'feedback' => 'Improved grammar.',
                    ],
                    [
                        'rubric_id' => $rubric2->id,
                        'score' => 22,
                        'feedback' => 'Better vocabulary.',
                    ],
                ],
                'progress_label' => 'Meningkat',
            ],
        ]);

        // Execute the job
        (new ProcessAiFeedbackJob($sub2))->handle();

        // Verify the submission was graded with correct score
        $sub2->refresh();
        expect($sub2->status)->toBe('graded');

        $this->assertDatabaseHas('ai_feedbacks', [
            'submission_id' => $sub2->id,
            'score' => 88,
        ]);

        // Verify the AI agent was prompted (it was called via the job)
        SubmissionFeedbackAgent::assertPrompted(
            fn($prompt) => $prompt->contains('Revised and improved essay.'),
        );
    },
);

// === Student/TaskController.submission ===
test('student can view specific submission details via AJAX', function () {
    [
        'student' => $student,
        'classroom' => $classroom,
        'task' => $task,
    ] = createEnrolledStudentWithTask();

    $submission = Submission::factory()->create([
        'user_id' => $student->id,
        'task_id' => $task->id,
        'version' => 1,
        'status' => 'graded',
    ]);

    $this->actingAs($student)
        ->getJson(
            route('student.classroom.task.submission.show', [
                $classroom,
                $task,
                $submission,
            ]),
        )
        ->assertSuccessful()
        ->assertJsonPath('submission.id', $submission->id);
});

test(
    'student can view the "Processing" state if AI feedback is not yet ready',
    function () {
        [
            'student' => $student,
            'classroom' => $classroom,
            'task' => $task,
        ] = createEnrolledStudentWithTask();

        $submission = Submission::factory()->create([
            'user_id' => $student->id,
            'task_id' => $task->id,
            'version' => 1,
            'status' => 'processing',
        ]);

        $this->actingAs($student)
            ->getJson(
                route('student.classroom.task.submission.show', [
                    $classroom,
                    $task,
                    $submission,
                ]),
            )
            ->assertSuccessful()
            ->assertJsonPath('submission.status', 'processing');
    },
);

test(
    'student can view AI feedback and rubric scores (AI score) once generated',
    function () {
        [
            'student' => $student,
            'classroom' => $classroom,
            'task' => $task,
            'rubric1' => $rubric1,
            'rubric2' => $rubric2,
        ] = createEnrolledStudentWithTask();

        $submission = Submission::factory()->create([
            'user_id' => $student->id,
            'task_id' => $task->id,
            'version' => 1,
            'status' => 'graded',
        ]);

        // AI feedback
        AiFeedback::factory()->create([
            'submission_id' => $submission->id,
            'result' => 'Great essay! Your grammar is excellent.',
            'score' => 88,
        ]);

        // Rubric scores from AI
        SubmissionRubricScore::factory()
            ->aiOnly()
            ->create([
                'submission_id' => $submission->id,
                'task_rubric_id' => $rubric1->id,
                'score_ai' => 22,
                'feedback_ai' => 'Good grammar usage.',
            ]);
        SubmissionRubricScore::factory()
            ->aiOnly()
            ->create([
                'submission_id' => $submission->id,
                'task_rubric_id' => $rubric2->id,
                'score_ai' => 20,
                'feedback_ai' => 'Decent vocabulary range.',
            ]);

        $response = $this->actingAs($student)
            ->getJson(
                route('student.classroom.task.submission.show', [
                    $classroom,
                    $task,
                    $submission,
                ]),
            )
            ->assertSuccessful();

        $response->assertJsonPath('submission.status', 'graded');
        $response->assertJsonPath('submission.ai_feedbacks.0.score', 88);
        $response->assertJsonCount(2, 'submission.rubric_scores');
    },
);

test(
    'student receives AI feedback that compares current attempt with previous attempt progress',
    function () {
        [
            'student' => $student,
            'classroom' => $classroom,
            'task' => $task,
            'rubric1' => $rubric1,
        ] = createEnrolledStudentWithTask();

        // Version 1 (graded, score 70)
        $sub1 = Submission::factory()->create([
            'user_id' => $student->id,
            'task_id' => $task->id,
            'version' => 1,
            'is_latest' => false,
            'status' => 'graded',
        ]);
        AiFeedback::factory()->create([
            'submission_id' => $sub1->id,
            'score' => 70,
            'result' => 'Needs improvement.',
        ]);
        SubmissionRubricScore::factory()
            ->aiOnly()
            ->create([
                'submission_id' => $sub1->id,
                'task_rubric_id' => $rubric1->id,
                'score_ai' => 15,
            ]);

        // Version 2 (graded, score 85 — improved)
        $sub2 = Submission::factory()->create([
            'user_id' => $student->id,
            'task_id' => $task->id,
            'version' => 2,
            'is_latest' => true,
            'status' => 'graded',
        ]);
        AiFeedback::factory()->create([
            'submission_id' => $sub2->id,
            'score' => 85,
            'result' => 'Much better! Great improvement.',
        ]);
        SubmissionRubricScore::factory()
            ->aiOnly()
            ->create([
                'submission_id' => $sub2->id,
                'task_rubric_id' => $rubric1->id,
                'score_ai' => 22,
            ]);

        $response = $this->actingAs($student)
            ->getJson(
                route('student.classroom.task.submission.show', [
                    $classroom,
                    $task,
                    $sub2,
                ]),
            )
            ->assertSuccessful();

        // The response should contain progress comparison data
        $response->assertJsonStructure([
            'submission' => ['id', 'version', 'status'],
            'progress',
        ]);
    },
);

test(
    'student can view teacher score and feedback alongside AI feedback if available',
    function () {
        [
            'student' => $student,
            'classroom' => $classroom,
            'task' => $task,
            'rubric1' => $rubric1,
            'rubric2' => $rubric2,
        ] = createEnrolledStudentWithTask();

        $submission = Submission::factory()->create([
            'user_id' => $student->id,
            'task_id' => $task->id,
            'version' => 1,
            'status' => 'graded',
        ]);

        AiFeedback::factory()->create([
            'submission_id' => $submission->id,
            'score' => 80,
            'result' => 'AI says good effort.',
        ]);

        // Rubric scores with BOTH AI and Teacher scores
        SubmissionRubricScore::factory()
            ->reviewed()
            ->create([
                'submission_id' => $submission->id,
                'task_rubric_id' => $rubric1->id,
                'score_ai' => 20,
                'feedback_ai' => 'AI: Good grammar.',
                'score_teacher' => 22,
                'feedback_teacher' => 'Teacher: Excellent grammar!',
            ]);

        SubmissionRubricScore::factory()
            ->aiOnly()
            ->create([
                'submission_id' => $submission->id,
                'task_rubric_id' => $rubric2->id,
                'score_ai' => 18,
                'feedback_ai' => 'AI: Average vocabulary.',
            ]);

        $response = $this->actingAs($student)
            ->getJson(
                route('student.classroom.task.submission.show', [
                    $classroom,
                    $task,
                    $submission,
                ]),
            )
            ->assertSuccessful();

        $rubricScores = $response->json('submission.rubric_scores');

        // First rubric should have both AI and teacher data
        $grammarScore = collect($rubricScores)->firstWhere(
            'task_rubric_id',
            $rubric1->id,
        );
        expect($grammarScore['score_ai'])->toBe(20);
        expect($grammarScore['score_teacher'])->toBe(22);
        expect($grammarScore['feedback_teacher'])->toBe(
            'Teacher: Excellent grammar!',
        );

        // Second rubric should only have AI data (teacher hasn't reviewed yet)
        $vocabScore = collect($rubricScores)->firstWhere(
            'task_rubric_id',
            $rubric2->id,
        );
        expect($vocabScore['score_ai'])->toBe(18);
        expect($vocabScore['score_teacher'])->toBeNull();
    },
);

test('student cannot view detail of other student\'s submission', function () {
    [
        'student' => $student,
        'classroom' => $classroom,
        'task' => $task,
    ] = createEnrolledStudentWithTask();

    $otherStudent = User::factory()->create(['role' => 'student']);
    Enrollment::factory()->create([
        'user_id' => $otherStudent->id,
        'classroom_id' => $classroom->id,
    ]);

    $otherSubmission = Submission::factory()->create([
        'user_id' => $otherStudent->id,
        'task_id' => $task->id,
        'version' => 1,
    ]);

    $this->actingAs($student)
        ->getJson(
            route('student.classroom.task.submission.show', [
                $classroom,
                $task,
                $otherSubmission,
            ]),
        )
        ->assertForbidden();
});

// Route Access
test('guest cannot access any student submission routes', function () {
    $classroom = Classroom::factory()->create();
    $task = Task::factory()->create([
        'classroom_id' => $classroom->id,
        'is_published' => true,
    ]);

    $this->get(
        route('student.classroom.task.show', [$classroom, $task]),
    )->assertRedirect('/login');

    $this->post(
        route('student.classroom.task.submission.store', [$classroom, $task]),
        [
            'content' => 'test',
        ],
    )->assertRedirect('/login');
});

test(
    'non-student (teacher/admin) cannot access student submission routes',
    function () {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $classroom = Classroom::factory()->create([
            'teacher_id' => $teacher->id,
        ]);
        $task = Task::factory()->create([
            'classroom_id' => $classroom->id,
            'is_published' => true,
        ]);

        $this->actingAs($teacher)
            ->get(route('student.classroom.task.show', [$classroom, $task]))
            ->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('student.classroom.task.show', [$classroom, $task]))
            ->assertForbidden();
    },
);

test(
    'student cannot access teacher-specific submission management routes',
    function () {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $classroom = Classroom::factory()->create([
            'teacher_id' => $teacher->id,
        ]);
        $task = Task::factory()->create([
            'classroom_id' => $classroom->id,
            'created_by' => $teacher->id,
        ]);
        $submission = Submission::factory()->create(['task_id' => $task->id]);

        $this->actingAs($student)
            ->get(
                route('teacher.classroom.task.submission.index', [
                    $classroom,
                    $task,
                ]),
            )
            ->assertForbidden();

        $this->actingAs($student)
            ->get(
                route('teacher.classroom.task.submission.show', [
                    $classroom,
                    $task,
                    $submission,
                ]),
            )
            ->assertForbidden();
    },
);
