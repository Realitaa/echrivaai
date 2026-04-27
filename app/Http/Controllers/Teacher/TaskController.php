<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Classroom $classroom)
    {
        $this->authorizeClassroomAccess($classroom);

        $tasks = $classroom->tasks()->latest()->paginate(10);

        return Inertia::render('teacher/task/Index', [
            'tasks' => $tasks,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Classroom $classroom)
    {
        $this->authorizeClassroomAccess($classroom);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'deadline' => ['required', 'date', 'after_or_equal:today'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $classroom->tasks()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'deadline' => $validated['deadline'],
            'is_published' => $validated['is_published'] ?? false,
            'created_by' => auth()->id(),
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Task created successfully!',
        ]);

        return to_route('teacher.classroom.show', $classroom);
    }

    /**
     * Display the specified resource.
     */
    public function show(Classroom $classroom, Task $task)
    {
        $this->authorizeTaskAccess($classroom, $task);

        return Inertia::render('teacher/task/Show', [
            'task' => $task,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Classroom $classroom, Task $task)
    {
        $this->authorizeTaskAccess($classroom, $task);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'deadline' => ['required', 'date', 'after_or_equal:today'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $task->update($validated);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Task updated successfully!',
        ]);

        return to_route('teacher.classroom.show', $classroom);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Classroom $classroom, Task $task)
    {
        $this->authorizeTaskAccess($classroom, $task);

        if ($task->hasSubmission()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Task cannot be deleted because it has submissions!',
            ]);

            return to_route('teacher.classroom.show', $classroom);
        }

        $task->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Task deleted successfully!',
        ]);

        return to_route('teacher.classroom.index');
    }

    /**
     * Ensure the authenticated teacher owns the classroom.
     */
    private function authorizeClassroomAccess(Classroom $classroom): void
    {
        abort_if($classroom->teacher_id !== auth()->id(), 403);
    }

    /**
     * Ensure the task belongs to the classroom and the teacher owns it.
     */
    private function authorizeTaskAccess(Classroom $classroom, Task $task): void
    {
        $this->authorizeClassroomAccess($classroom);
        abort_if($task->classroom_id !== $classroom->id, 404);
    }
}