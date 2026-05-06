<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreSubmissionRequest;
use App\Models\Classroom;
use App\Models\Submission;
use App\Models\Task;
use App\Services\Student\SubmissionService;
use App\Services\FileService;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Illuminate\Routing\Attributes\Controllers\Authorize;

class SubmissionController extends Controller
{
    public function __construct(
        protected SubmissionService $submissionService,
        protected FileService $fileService,
    ) {}

    /**
     * Display submission detail via AJAX (JSON response).
     */
    #[Authorize('viewAsStudent', 'submission')]
    public function show(
        Classroom $classroom,
        Task $task,
        Submission $submission,
    ) {
        abort_if($task->classroom_id !== $classroom->id, 404);
        abort_if($submission->task_id !== $task->id, 404);

        $data = $this->submissionService->getSubmissionDetail($submission);

        return response()->json($data);
    }

    /**
     * Store a new submission for a task.
     */
    #[Authorize('viewAsStudent', 'task')]
    public function store(
        StoreSubmissionRequest $request,
        Classroom $classroom,
        Task $task,
    ) {
        abort_if($task->classroom_id !== $classroom->id, 404);

        // Check deadline (now <= deadline, inclusive)
        if ($task->deadline && Carbon::now()->gt($task->deadline)) {
            $files = $request->validated('temporary_file_ids') ?? [];
            foreach ($files as $fileId) {
                $this->fileService->deleteTempFileById($fileId);
            }

            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'The deadline for this task has passed.',
            ]);

            return to_route('student.classroom.task.show', [$classroom, $task]);
        }

        // Check no processing submission exists (concurrency lock)
        if (
            $this->submissionService->hasProcessingSubmission(
                auth()->id(),
                $task->id,
            )
        ) {
            $files = $request->validated('temporary_file_ids') ?? [];
            foreach ($files as $fileId) {
                $this->fileService->deleteTempFileById($fileId);
            }

            Inertia::flash('toast', [
                'type' => 'error',
                'message' =>
                    'Your previous submission is still being processed. Please wait.',
            ]);

            return to_route('student.classroom.task.show', [$classroom, $task]);
        }

        $this->submissionService->createSubmission(
            $task,
            $request->validated(),
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' =>
                'Submission created successfully! Please wait while AI processes your feedback.',
        ]);

        return to_route('student.classroom.task.show', [$classroom, $task]);
    }
}
