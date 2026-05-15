<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreClassroomRequest;
use App\Http\Requests\Teacher\UpdateClassroomRequest;
use App\Models\Classroom;
use App\Services\Teacher\ClassroomService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Inertia\Inertia;

class ClassroomController extends Controller
{
    public function __construct(protected ClassroomService $classroomService) {}

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

        Cache::forget("sidebar_user_v1_{$request->user()->id}");

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('flash.teacher.classroom.created'),
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
    public function update(
        UpdateClassroomRequest $request,
        Classroom $classroom,
    ) {
        $this->classroomService->updateClassroom(
            $classroom,
            $request->validated(),
        );

        Cache::forget("sidebar_user_v1_{$classroom->teacher_id}");

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('flash.teacher.classroom.updated'),
        ]);

        return to_route('teacher.classroom.index');
    }

    #[Authorize('delete', 'classroom')]
    public function destroy(Classroom $classroom)
    {
        if (!$this->classroomService->deleteClassroom($classroom)) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' =>
                    'Classroom cannot be deleted because it has active tasks.',
            ]);

            return back();
        }

        Cache::forget("sidebar_user_v1_{$classroom->teacher_id}");

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('flash.teacher.classroom.deleted'),
        ]);

        return to_route('teacher.classroom.index');
    }
}
