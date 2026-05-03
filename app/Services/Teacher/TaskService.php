<?php

namespace App\Services\Teacher;

use App\Models\Classroom;
use App\Models\Task;
use App\Models\File;
use App\Models\TemporaryFile;
use Illuminate\Support\Facades\Storage;
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

    public function attachFiles(Task $task, array $fileIds)
    {
        foreach ($fileIds as $fileId) {
            $file = File::find($fileId);
            if ($file) {
                $file->fileable_type = Task::class;
                $file->fileable_id = $task->id;
                $file->save();
                continue;
            }

            $tempFile = TemporaryFile::find($fileId);
            if ($tempFile) {
                $oldPath = "tmp/$tempFile->filename";
                $newPath = "tasks/$tempFile->filename";

                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->move($oldPath, $newPath);
                }

                $task->files()->create([
                    'path' => $newPath,
                    'filename' => $tempFile->filename,
                    'original_name' => $tempFile->original_name,
                    'mime_type' =>
                        Storage::disk('public')->mimeType($newPath) ??
                        'application/octet-stream',
                    'size' => Storage::disk('public')->size($newPath) ?? 0,
                    'uploaded_by' => auth()->id(),
                ]);

                $tempFile->delete();
            }
        }
    }
}
