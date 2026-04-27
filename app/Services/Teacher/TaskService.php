<?php

namespace App\Services\Teacher;

use App\Models\Classroom;
use App\Models\Task;

use Illuminate\Support\Facades\DB;

class TaskService
{
    public function getPaginatedTasks(Classroom $classroom)
    {
        return $classroom->tasks()->latest()->paginate(10);
    }

    public function createTask(Classroom $classroom, array $data)
    {
        return DB::transaction(function () use ($classroom, $data) {
            $task = $classroom->tasks()->create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'deadline' => $data['deadline'],
                'is_published' => $data['is_published'] ?? false,
                'created_by' => auth()->id(),
            ]);

            if (!empty($data['rubrics'])) {
                $task->rubrics()->createMany($data['rubrics']);
            }

            return $task;
        });
    }

    public function updateTask(Task $task, array $data)
    {
        return DB::transaction(function () use ($task, $data) {
            $task->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'deadline' => $data['deadline'],
                'is_published' => $data['is_published'] ?? false,
            ]);

            if (isset($data['rubrics'])) {
                $task->rubrics()->delete();
                $task->rubrics()->createMany($data['rubrics']);
            }

            return $task;
        });
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
