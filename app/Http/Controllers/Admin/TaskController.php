<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classroom;
use App\Models\Task;
use App\Models\User;
use App\Services\Admin\TaskService;
use Inertia\Inertia;

class TaskController extends Controller
{
    public function __construct(protected TaskService $taskService) {}

    public function index(Request $request)
    {
        $tasks = $this->taskService->getPaginatedTasks($request);
        $classrooms = Classroom::select('id', 'name')->orderBy('name', 'asc')->get();
        $teachers = User::select('id', 'name')->where('role', 'teacher')->orderBy('name', 'asc')->get();

        return Inertia::render('admin/Task', [
            'tasks' => $tasks,
            'classrooms' => $classrooms,
            'teachers' => $teachers,
            'filters' => $request->only([
                'search',
                'teacher_id',
                'classroom_id',
                'is_published',
            ]),
        ]);
    }

    public function destroy(Task $task)
    {
        if (!$this->taskService->deleteTask($task)) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' =>
                    'Task cannot be deleted because it has submissions.',
            ]);

            return back();
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Task deleted successfully.',
        ]);

        return to_route('admin.task.index');
    }
}
