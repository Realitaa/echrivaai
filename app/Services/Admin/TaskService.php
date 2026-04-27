<?php

namespace App\Services\Admin;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskService
{
    public function getPaginatedTasks(Request $request)
    {
        return Task::query()
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
            })
            ->latest()
            ->paginate(10);
    }

    public function deleteTask(Task $task): bool
    {
        if ($task->hasSubmission()) {
            return false;
        }

        $task->delete();

        return true;
    }
}
