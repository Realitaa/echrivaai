<?php

use App\Models\User;
use App\Models\Classroom;
use App\Models\Task;
use App\Models\TaskRubric;
use App\Models\Submission;
use App\Models\SubmissionRubricScore;
use Inertia\Testing\AssertableInertia as Assert;

// === Teacher/SubmissionController.index ===

test('teacher can view submission list card inside a task', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    User::factory()->create(['role' => 'student']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $task = Task::factory()->create(['classroom_id' => $classroom->id, 'created_by' => $teacher->id]);
    
    Submission::factory()->count(3)->create([
        'task_id' => $task->id,
    ]);

    $this->actingAs($teacher)
        ->get(route('teacher.classroom.task.submission.index', [$classroom, $task]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) =>
            $page->component('teacher/submission/Index')
                 ->has('submissions.data', 3)
        );
});

test('teacher cannot view submission list from other task teacher', function () {
    $teacher1 = User::factory()->create(['role' => 'teacher']);
    $teacher2 = User::factory()->create(['role' => 'teacher']);
    
    $classroom2 = Classroom::factory()->create(['teacher_id' => $teacher2->id]);
    $task2 = Task::factory()->create(['classroom_id' => $classroom2->id, 'created_by' => $teacher2->id]);

    $this->actingAs($teacher1)
        ->get(route('teacher.classroom.task.submission.index', [$classroom2, $task2]))
        ->assertForbidden();
});

// === Teacher/SubmissionController.show ===

test('teacher can view submission detail', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $student = User::factory()->create(['role' => 'student']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $task = Task::factory()->create(['classroom_id' => $classroom->id, 'created_by' => $teacher->id]);
    $submission = Submission::factory()->create(['task_id' => $task->id, 'user_id' => $student->id]);

    $this->actingAs($teacher)
        ->get(route('teacher.classroom.task.submission.show', [$classroom, $task, $submission]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) =>
            $page->component('teacher/submission/Show')
                 ->where('submission.id', $submission->id)
        );
});

test('teacher cannot view submission detail from other task teacher', function () {
    $teacher1 = User::factory()->create(['role' => 'teacher']);
    $teacher2 = User::factory()->create(['role' => 'teacher']);
    $student = User::factory()->create(['role' => 'student']);
    
    $classroom2 = Classroom::factory()->create(['teacher_id' => $teacher2->id]);
    $task2 = Task::factory()->create(['classroom_id' => $classroom2->id, 'created_by' => $teacher2->id]);
    $submission2 = Submission::factory()->create(['task_id' => $task2->id, 'user_id' => $student->id]);

    $this->actingAs($teacher1)
        ->get(route('teacher.classroom.task.submission.show', [$classroom2, $task2, $submission2]))
        ->assertForbidden();
});

// === Teacher/SubmissionController.feedback ===

test('teacher can give score and feedback into submission per rubric', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $student = User::factory()->create(['role' => 'student']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $task = Task::factory()->create(['classroom_id' => $classroom->id, 'created_by' => $teacher->id]);
    
    $rubric1 = TaskRubric::factory()->create(['task_id' => $task->id, 'max_score' => 50]);
    $rubric2 = TaskRubric::factory()->create(['task_id' => $task->id, 'max_score' => 50]);
    
    $submission = Submission::factory()->create(['task_id' => $task->id, 'user_id' => $student->id]);
    
    // AI biasanya sudah membuat record penilaian sebelumnya, Dosen tinggal update
    $score1 = SubmissionRubricScore::factory()->create(['submission_id' => $submission->id, 'task_rubric_id' => $rubric1->id]);
    $score2 = SubmissionRubricScore::factory()->create(['submission_id' => $submission->id, 'task_rubric_id' => $rubric2->id]);

    $response = $this->actingAs($teacher)
        ->put(route('teacher.classroom.task.submission.feedback', [$classroom, $task, $submission]), [
            'rubrics' => [
                [
                    'task_rubric_id' => $rubric1->id,
                    'score_teacher' => 45,
                    'feedback_teacher' => 'Excellent use of complex sentences.',
                ],
                [
                    'task_rubric_id' => $rubric2->id,
                    'score_teacher' => 30,
                    'feedback_teacher' => 'Watch out for verb conjugations.',
                ],
            ],
        ]);

    $response->assertRedirect(route('teacher.classroom.task.submission.show', [$classroom, $task, $submission]));
    
    $response->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' => 'Feedback submitted successfully!',
    ]);
    
    $this->assertDatabaseHas('submission_rubric_scores', [
        'id' => $score1->id,
        'score_teacher' => 45,
        'feedback_teacher' => 'Excellent use of complex sentences.',
    ]);

    $this->assertDatabaseHas('submission_rubric_scores', [
        'id' => $score2->id,
        'score_teacher' => 30,
        'feedback_teacher' => 'Watch out for verb conjugations.',
    ]);
});

test('teacher cannot give teacher score exceeding rubric max score', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $student = User::factory()->create(['role' => 'student']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $task = Task::factory()->create(['classroom_id' => $classroom->id, 'created_by' => $teacher->id]);
    
    // Max score adalah 20
    $rubric = TaskRubric::factory()->create(['task_id' => $task->id, 'max_score' => 20]);
    $submission = Submission::factory()->create(['task_id' => $task->id, 'user_id' => $student->id]);
    SubmissionRubricScore::factory()->create(['submission_id' => $submission->id, 'task_rubric_id' => $rubric->id]);

    $this->actingAs($teacher)
        ->put(route('teacher.classroom.task.submission.feedback', [$classroom, $task, $submission]), [
            'rubrics' => [
                [
                    'task_rubric_id' => $rubric->id,
                    'score_teacher' => 25, // Melebihi max_score
                    'feedback_teacher' => 'Good job.',
                ],
            ],
        ])
        ->assertSessionHasErrors('rubrics.0.score_teacher');
});

test('teacher cannot give score without feedback per rubric', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $student = User::factory()->create(['role' => 'student']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $task = Task::factory()->create(['classroom_id' => $classroom->id, 'created_by' => $teacher->id]);
    
    $rubric = TaskRubric::factory()->create(['task_id' => $task->id, 'max_score' => 50]);
    $submission = Submission::factory()->create(['task_id' => $task->id, 'user_id' => $student->id]);
    SubmissionRubricScore::factory()->create(['submission_id' => $submission->id, 'task_rubric_id' => $rubric->id]);

    $this->actingAs($teacher)
        ->put(route('teacher.classroom.task.submission.feedback', [$classroom, $task, $submission]), [
            'rubrics' => [
                [
                    'task_rubric_id' => $rubric->id,
                    'score_teacher' => 40,
                    'feedback_teacher' => '', // Kosong
                ],
            ],
        ])
        ->assertSessionHasErrors('rubrics.0.feedback_teacher');
});

test('teacher cannot give feedback without score per rubric', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $student = User::factory()->create(['role' => 'student']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $task = Task::factory()->create(['classroom_id' => $classroom->id, 'created_by' => $teacher->id]);
    
    $rubric = TaskRubric::factory()->create(['task_id' => $task->id, 'max_score' => 50]);
    $submission = Submission::factory()->create(['task_id' => $task->id, 'user_id' => $student->id]);
    SubmissionRubricScore::factory()->create(['submission_id' => $submission->id, 'task_rubric_id' => $rubric->id]);

    $this->actingAs($teacher)
        ->put(route('teacher.classroom.task.submission.feedback', [$classroom, $task, $submission]), [
            'rubrics' => [
                [
                    'task_rubric_id' => $rubric->id,
                    'score_teacher' => null, // Kosong
                    'feedback_teacher' => 'Good attempt.',
                ],
            ],
        ])
        ->assertSessionHasErrors('rubrics.0.score_teacher');
});

test('teacher cannot give score and feedback into other teacher submission', function () {
    $teacher1 = User::factory()->create(['role' => 'teacher']);
    $teacher2 = User::factory()->create(['role' => 'teacher']);
    $student = User::factory()->create(['role' => 'student']);
    
    $classroom2 = Classroom::factory()->create(['teacher_id' => $teacher2->id]);
    $task2 = Task::factory()->create(['classroom_id' => $classroom2->id, 'created_by' => $teacher2->id]);
    $rubric2 = TaskRubric::factory()->create(['task_id' => $task2->id, 'max_score' => 50]);
    $submission2 = Submission::factory()->create(['task_id' => $task2->id, 'user_id' => $student->id]);
    SubmissionRubricScore::factory()->create(['submission_id' => $submission2->id, 'task_rubric_id' => $rubric2->id]);

    $this->actingAs($teacher1)
        ->put(route('teacher.classroom.task.submission.feedback', [$classroom2, $task2, $submission2]), [
            'rubrics' => [
                [
                    'task_rubric_id' => $rubric2->id,
                    'score_teacher' => 40,
                    'feedback_teacher' => 'Hacked feedback',
                ],
            ],
        ])
        ->assertForbidden();
});

// === Route Access ===

test('non-teacher cannot access teacher submission routes', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $student = User::factory()->create(['role' => 'student']); 
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $task = Task::factory()->create(['classroom_id' => $classroom->id, 'created_by' => $teacher->id]);
    $rubric = TaskRubric::factory()->create(['task_id' => $task->id]);
    $submission = Submission::factory()->create(['task_id' => $task->id, 'user_id' => $student->id]);

    $this->actingAs($student)
        ->get(route('teacher.classroom.task.submission.show', [$classroom, $task, $submission]))
        ->assertForbidden();
        
    $this->actingAs($student)
        ->put(route('teacher.classroom.task.submission.feedback', [$classroom, $task, $submission]), [
            'rubrics' => [
                [
                    'task_rubric_id' => $rubric->id,
                    'score_teacher' => 50,
                    'feedback_teacher' => 'Self grading',
                ],
            ],
        ])
        ->assertForbidden();
});

test('guest cannot access teacher submission routes', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $student = User::factory()->create(['role' => 'student']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $task = Task::factory()->create(['classroom_id' => $classroom->id, 'created_by' => $teacher->id]);
    $submission = Submission::factory()->create(['task_id' => $task->id, 'user_id' => $student->id]);

    $this->get(route('teacher.classroom.task.submission.show', [$classroom, $task, $submission]))
        ->assertRedirect('/login');
});