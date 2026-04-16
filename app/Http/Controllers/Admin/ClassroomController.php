<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use Inertia\Inertia;

class ClassroomController extends Controller
{
    public function index()
    {
        $classroom = Classroom::with('teacher')
            ->when(request('teacher_id'), function ($query) {
                $query->where('teacher_id', request('teacher_id'));
            })
            ->when(request('search'), function ($query) {
                $search = request('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('admin/Classroom', [
            'classroom' => $classroom,
        ]);
    }

    public function destroy(Classroom $classroom)
    {
        if ($classroom->hasActiveTasks()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Classroom cannot be deleted because it has active tasks.',
            ]);

            return to_route('admin.classroom.index');
        }

        $classroom->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Classroom deleted successfully.',
        ]);

        return to_route('admin.classroom.index');
    }
}
