<?php

namespace App\Services\Admin;

use App\Models\Classroom;
use Illuminate\Http\Request;

class ClassroomService
{
    public function getPaginatedClassrooms(Request $request)
    {
        return Classroom::with('teacher')
            ->when($request->teacher_id, function ($query) use ($request) {
                $query->where('teacher_id', $request->teacher_id);
            })
            ->when($request->search, function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10);
    }

    public function deleteClassroom(Classroom $classroom): bool
    {
        if ($classroom->hasActiveTasks()) {
            return false;
        }

        $classroom->delete();

        return true;
    }

    public function getEnrollments(Classroom $classroom)
    {
        return $classroom->enrollments()
            ->with('user:id,name,email')
            ->get();
    }
}
