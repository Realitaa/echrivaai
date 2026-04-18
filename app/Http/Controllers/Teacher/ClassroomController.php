<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classroom;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Routing\Attributes\Controllers\Authorize;

class ClassroomController extends Controller
{
    public function index()
    {
        $classrooms = Classroom::where('teacher_id', auth()->id())
            ->withCount('tasks')
            ->withCount('enrollments')
            ->latest()
            ->paginate(10);

        return Inertia::render('teacher/classroom/Index', [
            'classrooms' => $classrooms,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        Classroom::create([
            'name' => $request->name,
            'description' => $request->description,
            'teacher_id' => auth()->id(),
            'code' => Str::random(8),
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Classroom created successfully!',
        ]);

        return to_route('teacher.classroom.index');
    }

    #[Authorize('view', 'classroom')]
    public function show(Classroom $classroom)
    {
        $classroom->loadCount(['tasks', 'enrollments']);
        $classroom->load(['students']);

        return Inertia::render('teacher/classroom/Show', [
            'classroom' => $classroom,
        ]);
    }

    #[Authorize('update', 'classroom')]
    public function update(Request $request, Classroom $classroom)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $classroom->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Classroom updated successfully!',
        ]);

        return to_route('teacher.classroom.index');
    }

    #[Authorize('delete', 'classroom')]
    public function destroy(Classroom $classroom)
    {
        if ($classroom->hasActiveTasks()) {
            return redirect()->route('teacher.classroom.index')
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Classroom cannot be deleted because it has active tasks.',
                ]);
        }

        $classroom->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Classroom deleted successfully!',
        ]);

        return to_route('teacher.classroom.index');
    }
}
