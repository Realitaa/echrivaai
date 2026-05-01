<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use Inertia\Inertia;

class TaskController extends Controller
{
    public function index(Classroom $classroom)
    {
        if (!$classroom->enrollments()->where('user_id', auth()->id())->exists()) {
            abort(403);
        }

        return Inertia::render('student/task/Index', [
            'tasks' => $classroom->publishedTasks()->latest()->paginate(10)
        ]);
    }
}
