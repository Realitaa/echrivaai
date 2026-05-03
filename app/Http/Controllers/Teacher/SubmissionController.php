<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Services\Teacher\SubmissionService;
use App\Models\Classroom;
use App\Models\Task;
use App\Models\Submission;
use App\Http\Requests\Teacher\FeedbackSubmissionRequest;
use Inertia\Inertia;

class SubmissionController extends Controller
{
    public function __construct(
        protected SubmissionService $submissionService,
    ) {}

    public function index(Classroom $classroom, Task $task)
    {
        $this->authorizeAccess($classroom, $task);

        $submissions = $this->submissionService->getPaginatedSubmissions($task);

        return Inertia::render('teacher/submission/Index', [
            'submissions' => $submissions,
        ]);
    }

    public function show(
        Classroom $classroom,
        Task $task,
        Submission $submission,
    ) {
        $this->authorizeAccess($classroom, $task, $submission);

        return Inertia::render('teacher/submission/Show', [
            'submission' => $submission,
        ]);
    }

    public function feedback(
        FeedbackSubmissionRequest $request,
        Classroom $classroom,
        Task $task,
        Submission $submission,
    ) {
        $this->submissionService->updateFeedback(
            $submission,
            $request->validated(),
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Feedback submitted successfully!',
        ]);

        return to_route('teacher.classroom.task.submission.show', [
            $classroom,
            $task,
            $submission,
        ]);
    }

    private function authorizeAccess(
        Classroom $classroom,
        Task $task,
        Submission $submission = null,
    ): void {
        abort_if($classroom->teacher_id !== auth()->id(), 403);
        abort_if($task->classroom_id !== $classroom->id, 403);
        if ($submission) {
            abort_if($submission->task_id !== $task->id, 403);
        }
    }
}
