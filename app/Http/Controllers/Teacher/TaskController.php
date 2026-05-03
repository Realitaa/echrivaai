<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreTaskRequest;
use App\Http\Requests\Teacher\UpdateTaskRequest;
use App\Models\Classroom;
use App\Models\Task;
use App\Services\Teacher\TaskService;
use Inertia\Inertia;

class TaskController extends Controller
{
    public function __construct(protected TaskService $taskService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Classroom $classroom)
    {
        $this->authorizeClassroomAccess($classroom);

        $tasks = $this->taskService->getPaginatedTasks($classroom);

        return Inertia::render('teacher/task/Index', [
            'tasks' => $tasks,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request, Classroom $classroom)
    {
        $this->authorizeClassroomAccess($classroom);

        $task = $this->taskService->createTask(
            $classroom,
            $request->validated(),
        );

        if ($request->has('attachments')) {
            $this->taskService->attachFiles(
                $task,
                $request->input('attachments'),
            );
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Task created successfully!',
        ]);

        return to_route('teacher.classroom.task.index', $classroom);
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
    public function update(
        UpdateTaskRequest $request,
        Classroom $classroom,
        Task $task,
    ) {
        $this->authorizeTaskAccess($classroom, $task);

        if ($task->is_published) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'You cannot update a published task!',
            ]);

            return to_route('teacher.classroom.show', $classroom);
        }

        $this->taskService->updateTask($task, $request->validated());

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

        if ($task->is_published) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'You cannot delete a published task!',
            ]);

            return to_route('teacher.classroom.show', $classroom);
        }

        if (!$this->taskService->deleteTask($task)) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' =>
                    'Task cannot be deleted because it has submissions!',
            ]);

            return to_route('teacher.classroom.show', $classroom);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Task deleted successfully!',
        ]);

        return to_route('teacher.classroom.show', $classroom);
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
