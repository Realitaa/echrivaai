<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Task;
use Inertia\Inertia;
use Illuminate\Routing\Attributes\Controllers\Authorize;

class TaskController extends Controller
{
    #[Authorize('viewAsStudent', 'classroom')]
    public function index(Classroom $classroom)
    {
        return Inertia::render('student/task/Index', [
            'tasks' => $classroom->publishedTasks()->latest()->paginate(10),
        ]);
    }

    #[Authorize('viewAsStudent', 'task')]
    public function show(Classroom $classroom, Task $task)
    {
        // Ensure task belongs to this classroom
        abort_if($task->classroom_id !== $classroom->id, 404);

        // Get the authenticated student's submissions for this task
        $submissions = $task
            ->submissions()
            ->where('user_id', auth()->id())
            ->orderBy('version', 'desc')
            ->get();

        return Inertia::render('student/task/Show', [
            'task' => $task,
            'submissions' => $submissions,
        ]);
    }
}
