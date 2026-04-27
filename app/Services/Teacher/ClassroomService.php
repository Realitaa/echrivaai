<?php

namespace App\Services\Teacher;

use App\Models\Classroom;
use Illuminate\Support\Str;

class ClassroomService
{
    public function getPaginatedClassrooms()
    {
        return Classroom::where('teacher_id', auth()->id())
            ->withCount('tasks')
            ->withCount('enrollments')
            ->latest()
            ->paginate(10);
    }

    public function createClassroom(array $data)
    {
        return Classroom::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'teacher_id' => auth()->id(),
            'code' => Str::random(8),
        ]);
    }

    public function loadClassroomDetails(Classroom $classroom)
    {
        $classroom->loadCount(['tasks', 'enrollments']);
        $classroom->load(['students']);
        return $classroom;
    }

    public function updateClassroom(Classroom $classroom, array $data)
    {
        return $classroom->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
    }

    public function deleteClassroom(Classroom $classroom): bool
    {
        if ($classroom->hasActiveTasks()) {
            return false;
        }

        $classroom->delete();
        return true;
    }
}
