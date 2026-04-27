<?php

namespace App\Services\Teacher;

use App\Models\Classroom;
use App\Models\Task;

class TaskService
{
    public function getPaginatedTasks(Classroom $classroom)
    {
        return $classroom->tasks()->latest()->paginate(10);
    }

    public function createTask(Classroom $classroom, array $data)
    {
        return $classroom->tasks()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'deadline' => $data['deadline'],
            'is_published' => $data['is_published'] ?? false,
            'created_by' => auth()->id(),
        ]);
    }

    public function updateTask(Task $task, array $data)
    {
        return $task->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'deadline' => $data['deadline'],
            'is_published' => $data['is_published'] ?? false,
        ]);
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
