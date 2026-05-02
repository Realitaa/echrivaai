<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreSubmissionRequest;
use App\Models\Classroom;
use App\Models\Submission;
use App\Models\Task;
use App\Services\Student\SubmissionService;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class SubmissionController extends Controller
{
    public function __construct(protected SubmissionService $submissionService)
    {
    }

    /**
     * Display submission detail via AJAX (JSON response).
     */
    public function show(Classroom $classroom, Task $task, Submission $submission)
    {
        $this->authorizeAccess($classroom, $task, $submission);

        $data = $this->submissionService->getSubmissionDetail($submission);

        return response()->json($data);
    }

    /**
     * Store a new submission for a task.
     */
    public function store(StoreSubmissionRequest $request, Classroom $classroom, Task $task)
    {
        $this->authorizeAccess($classroom, $task);

        // Check task is published
        if (! $task->is_published) {
            abort(403);
        }

        // Check deadline (now <= deadline, inclusive)
        if ($task->deadline && Carbon::now()->gt($task->deadline)) {
            Inertia::flash('toast', [
                'type'    => 'error',
                'message' => 'The deadline for this task has passed.',
            ]);

            return to_route('student.classroom.task.show', [$classroom, $task]);
        }

        // Check no processing submission exists (concurrency lock)
        if ($this->submissionService->hasProcessingSubmission(auth()->id(), $task->id)) {
            Inertia::flash('toast', [
                'type'    => 'error',
                'message' => 'Your previous submission is still being processed. Please wait.',
            ]);

            return to_route('student.classroom.task.show', [$classroom, $task]);
        }

        $this->submissionService->createSubmission($task, $request->validated());

        Inertia::flash('toast', [
            'type'    => 'success',
            'message' => 'Submission created successfully! Please wait while AI processes your feedback.',
        ]);

        return to_route('student.classroom.task.show', [$classroom, $task]);
    }

    /**
     * Authorize access: check enrollment, task belongs to classroom,
     * and optionally check submission ownership.
     */
    private function authorizeAccess(Classroom $classroom, Task $task, ?Submission $submission = null): void
    {
        // Check student is enrolled in the classroom
        $isEnrolled = $classroom->enrollments()
            ->where('user_id', auth()->id())
            ->exists();

        abort_if(! $isEnrolled, 403);

        // Check task belongs to classroom
        abort_if($task->classroom_id !== $classroom->id, 403);

        if ($submission) {
            // Check submission belongs to this task
            abort_if($submission->task_id !== $task->id, 403);

            // Check submission belongs to the authenticated student
            abort_if($submission->user_id !== auth()->id(), 403);
        }
    }
}
