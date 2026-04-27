<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreClassroomRequest;
use App\Http\Requests\Teacher\UpdateClassroomRequest;
use App\Models\Classroom;
use App\Services\Teacher\ClassroomService;
use Inertia\Inertia;
use Illuminate\Routing\Attributes\Controllers\Authorize;

class ClassroomController extends Controller
{
    public function __construct(protected ClassroomService $classroomService)
    {
    }

    public function index()
    {
        $classrooms = $this->classroomService->getPaginatedClassrooms();

        return Inertia::render('teacher/classroom/Index', [
            'classrooms' => $classrooms,
        ]);
    }

    public function store(StoreClassroomRequest $request)
    {
        $this->classroomService->createClassroom($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Classroom created successfully!',
        ]);

        return to_route('teacher.classroom.index');
    }

    #[Authorize('view', 'classroom')]
    public function show(Classroom $classroom)
    {
        $classroom = $this->classroomService->loadClassroomDetails($classroom);

        return Inertia::render('teacher/classroom/Show', [
            'classroom' => $classroom,
        ]);
    }

    #[Authorize('update', 'classroom')]
    public function update(UpdateClassroomRequest $request, Classroom $classroom)
    {
        $this->classroomService->updateClassroom($classroom, $request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Classroom updated successfully!',
        ]);

        return to_route('teacher.classroom.index');
    }

    #[Authorize('delete', 'classroom')]
    public function destroy(Classroom $classroom)
    {
        if (!$this->classroomService->deleteClassroom($classroom)) {
            return redirect()->route('teacher.classroom.index')
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Classroom cannot be deleted because it has active tasks.',
                ]);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Classroom deleted successfully!',
        ]);

        return to_route('teacher.classroom.index');
    }
}
