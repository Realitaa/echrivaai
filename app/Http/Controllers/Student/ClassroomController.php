<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Enrollment;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use App\Services\Teacher\ClassroomService as TeacherClassroomService;
use Illuminate\Support\Facades\Cache;

class ClassroomController extends Controller
{
    public function __construct(protected TeacherClassroomService $teacherClassroomService) {}
    
    public function index()
    {
        $classrooms = Classroom::whereHas('enrollments', fn($query) => $query->where('user_id', auth()->id()))
            ->with('teacher', fn($query) => $query->select('id', 'name'))
            ->paginate(10);

        return Inertia::render('student/classroom/Index', [
            'classrooms' => $classrooms,
        ]);
    }

    public function enroll(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $classroom = Classroom::where('code', $request->code)->first();

        if (!$classroom) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Invalid classroom code!',
            ]);
            return redirect()->route('student.classroom.index');
        }

        $isEnrolled = app(Enrollment::class)->isEnrolled(
            auth()->id(),
            $classroom->id,
        );

        if ($isEnrolled) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => __('flash.student.classroom.alreadyEnrolled'),
            ]);
            return redirect()->route('student.classroom.index');
        }

        Enrollment::create([
            'user_id' => auth()->id(),
            'classroom_id' => $classroom->id,
        ]);

        Cache::forget("sidebar_user_v1_{$request->user()->id}");

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('flash.student.classroom.enrolled'),
        ]);

        return redirect()->route('student.classroom.show', $classroom);
    }

    #[Authorize('viewAsStudent', 'classroom')]
    public function show(Classroom $classroom)
    {
        $classroom = $this->teacherClassroomService->loadClassroomDetails($classroom);
        
        return Inertia::render('student/classroom/Show', [
            'classroom' => $classroom,
        ]);
    }
}
