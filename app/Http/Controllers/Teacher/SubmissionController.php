<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Services\Teacher\SubmissionService as TeacherSubmissionService;
use App\Services\Student\SubmissionService as StudentSubmissionService;
use App\Models\Classroom;
use App\Models\Task;
use App\Models\Submission;
use App\Http\Requests\Teacher\FeedbackSubmissionRequest;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Routing\Attributes\Controllers\Authorize;

class SubmissionController extends Controller
{
    public function __construct(
        protected TeacherSubmissionService $teacherSubmissionService,
        protected StudentSubmissionService $studentSubmissionService,
    ) {}

    #[Authorize('view', 'task')]
    public function index(Classroom $classroom, Task $task)
    {
        abort_if($task->classroom_id !== $classroom->id, 404);

        $students = $this->teacherSubmissionService->getEnrolledStudentsWithSubmissions(
            $task,
        );

        return Inertia::render('teacher/submission/Index', [
            'task' => $task->load(['files', 'creator', 'rubrics']),
            'students' => $students,
        ]);
    }

    #[Authorize('view', 'task')]
    public function history(Classroom $classroom, Task $task, User $student)
    {
        abort_if($task->classroom_id !== $classroom->id, 404);

        $submissions = $this->teacherSubmissionService->getStudentHistory(
            $task,
            $student,
        );

        return response()->json([
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

        $data = $this->studentSubmissionService->getSubmissionDetail(
            $submission,
        );

        return response()->json($data);
    }

    #[Authorize('update', 'submission')]
    public function feedback(
        FeedbackSubmissionRequest $request,
        Classroom $classroom,
        Task $task,
        Submission $submission,
    ) {
        // abort_if($task->classroom_id !== $classroom->id, 404);
        // abort_if($submission->task_id !== $task->id, 404);

        // $this->submissionService->updateFeedback(
        //     $submission,
        //     $request->validated(),
        // );

        // Inertia::flash('toast', [
        //     'type' => 'success',
        //     'message' => 'Feedback submitted successfully!',
        // ]);

        // return to_route('teacher.classroom.task.submission.show', [
        //     $classroom,
        //     $task,
        //     $submission,
        // ]);
    }
}
