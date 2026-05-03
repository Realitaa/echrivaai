<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Task;
use Inertia\Inertia;

class TaskController extends Controller
{
    public function index(Classroom $classroom)
    {
        if (
            !$classroom->enrollments()->where('user_id', auth()->id())->exists()
        ) {
            abort(403);
        }

        return Inertia::render('student/task/Index', [
            'tasks' => $classroom->publishedTasks()->latest()->paginate(10),
        ]);
    }

    public function show(Classroom $classroom, Task $task)
    {
        if (
            !$classroom->enrollments()->where('user_id', auth()->id())->exists()
        ) {
            abort(403);
        }

        // Only published tasks are visible to students
        if (!$task->is_published) {
            abort(403);
        }

        // Ensure task belongs to this classroom
        abort_if($task->classroom_id !== $classroom->id, 403);

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
