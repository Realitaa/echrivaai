<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use Inertia\Inertia;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::query()
            ->with(['classroom.teacher'])
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $q->where('title', 'like', "%{$request->search}%")
                        ->orWhereHas('classroom.teacher', function ($q) use ($request) {
                            $q->where('name', 'like', "%{$request->search}%");
                        })
                        ->orWhereHas('classroom', function ($q) use ($request) {
                            $q->where('name', 'like', "%{$request->search}%");
                        });
                });
            })

            ->when($request->teacher_id, function ($q) use ($request) {
                $q->whereHas('classroom', function ($q) use ($request) {
                    $q->where('teacher_id', $request->teacher_id);
                });
            })

            ->when($request->classroom_id, fn($q) =>
                $q->where('classroom_id', $request->classroom_id)
            )

            ->when($request->has('is_published'), function ($q) use ($request) {
                $q->where('is_published', $request->is_published);
            });

        $tasks = $query->latest()->paginate(10);

        return Inertia::render('admin/Task', [
            'tasks' => $tasks,
            'filters' => $request->only(['search', 'teacher_id', 'classroom_id', 'is_published']),
        ]);
    }

    public function destroy(Task $task)
    {
        if ($task->hasSubmission()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Task cannot be deleted because it has submissions.',
            ]);

            return back();
        }

        $task->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Task deleted successfully.',
        ]);

        return to_route('admin.tasks.index');
    }
}
