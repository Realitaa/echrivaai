<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Enrollment;
use Inertia\Inertia;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    public function index()
    {
        $classrooms = Classroom::whereHas('enrollments', function ($query) {
            $query->where('user_id', auth()->id());
        })->paginate(10);

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

        $isEnrolled = app(Enrollment::class)->isEnrolled(auth()->id(), $classroom->id);

        if ($isEnrolled) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'You are already enrolled in this classroom!',
            ]);
            return redirect()->route('student.classroom.index');
        }

        Enrollment::create([
            'user_id' => auth()->id(),
            'classroom_id' => $classroom->id,
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Successfully enrolled in the classroom!',
        ]);

        return redirect()->route('student.classroom.show', $classroom);
    }

    public function show(Classroom $classroom)
    {
        $isEnrolled = app(Enrollment::class)->isEnrolled(auth()->id(), $classroom->id);

        abort_if(!$isEnrolled, 403);

        return Inertia::render('student/classroom/Show', [
            'classroom' => $classroom,
        ]);
    }
}
