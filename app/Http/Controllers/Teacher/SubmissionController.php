<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Services\Teacher\SubmissionService;
use App\Models\Classroom;
use App\Models\Task;
use App\Models\Submission;
use App\Http\Requests\Teacher\FeedbackSubmissionRequest;
use Inertia\Inertia;
use Illuminate\Routing\Attributes\Controllers\Authorize;

class SubmissionController extends Controller
{
    public function __construct(
        protected SubmissionService $submissionService,
    ) {}

    #[Authorize('view', 'task')]
    public function index(Classroom $classroom, Task $task)
    {
        abort_if($task->classroom_id !== $classroom->id, 404);

        $submissions = $this->submissionService->getPaginatedSubmissions($task);

        return Inertia::render('teacher/submission/Index', [
            'task' => $task->load(['files', 'creator', 'rubrics']),
            'submissions' => $submissions,
        ]);
    }

    #[Authorize('view', 'submission')]
    public function show(
        Classroom $classroom,
        Task $task,
        Submission $submission,
    ) {
        abort_if($task->classroom_id !== $classroom->id, 404);
        abort_if($submission->task_id !== $task->id, 404);

        return Inertia::render('teacher/submission/Show', [
            'submission' => $submission,
        ]);
    }

    #[Authorize('update', 'submission')]
    public function feedback(
        FeedbackSubmissionRequest $request,
        Classroom $classroom,
        Task $task,
        Submission $submission,
    ) {
        abort_if($task->classroom_id !== $classroom->id, 404);
        abort_if($submission->task_id !== $task->id, 404);

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
}
