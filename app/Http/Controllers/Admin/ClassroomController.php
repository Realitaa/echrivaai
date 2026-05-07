<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\User;
use App\Services\Admin\ClassroomService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassroomController extends Controller
{
    public function __construct(protected ClassroomService $classroomService) {}

    public function index(Request $request)
    {
        $classroom = $this->classroomService->getPaginatedClassrooms($request);
        $teachers = User::select('id', 'name')
            ->where('role', 'teacher')
            ->orderBy('name', 'asc')
            ->get();

        return Inertia::render('admin/Classroom', [
            'classroom' => $classroom,
            'filters' => $request->all(),
            'teachers' => $teachers,
        ]);
    }

    public function destroy(Classroom $classroom)
    {
        if (!$this->classroomService->deleteClassroom($classroom)) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' =>
                    'Classroom cannot be deleted because it has active tasks.',
            ]);

            return to_route('admin.classroom.index');
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Classroom deleted successfully.',
        ]);

        return to_route('admin.classroom.index');
    }

    public function enrollments(Classroom $classroom)
    {
        $enrollments = $this->classroomService->getEnrollments($classroom);

        return response()->json([
            'data' => $enrollments,
        ]);
    }
}
