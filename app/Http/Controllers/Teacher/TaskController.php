<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreTaskRequest;
use App\Http\Requests\Teacher\UpdateTaskRequest;
use App\Models\Classroom;
use App\Models\Task;
use App\Services\Teacher\TaskService;
use Inertia\Inertia;
use Illuminate\Routing\Attributes\Controllers\Authorize;

class TaskController extends Controller
{
    public function __construct(protected TaskService $taskService) {}

    /**
     * Display a listing of the resource.
     */
    #[Authorize('view', 'classroom')]
    public function index(Classroom $classroom)
    {
        $tasks = $this->taskService->getPaginatedTasks($classroom);
        $classroom->loadCount(['tasks', 'enrollments']);

        return Inertia::render('teacher/task/Index', [
            'classroom' => $classroom,
            'tasks' => $tasks,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    #[Authorize('view', 'classroom')]
    public function create(Classroom $classroom)
    {
        return Inertia::render('teacher/task/Form', [
            'classroom' => $classroom,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    #[Authorize('update', 'classroom')]
    public function store(StoreTaskRequest $request, Classroom $classroom)
    {
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

    #[Authorize('update', 'task')]
    public function edit(Classroom $classroom, Task $task)
    {
        abort_if($task->classroom_id !== $classroom->id, 404);

        if ($task->is_published) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'You cannot edit a published task!',
            ]);

            return to_route('teacher.classroom.task.index', $classroom);
        }

        return Inertia::render('teacher/task/Form', [
            'classroom' => $classroom,
            'task' => $task->load('files', 'rubrics'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    #[Authorize('update', 'task')]
    public function update(
        UpdateTaskRequest $request,
        Classroom $classroom,
        Task $task,
    ) {
        abort_if($task->classroom_id !== $classroom->id, 404);

        if ($task->is_published) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'You cannot update a published task!',
            ]);

            return to_route('teacher.classroom.task.index', $classroom);
        }

        $this->taskService->updateTask($task, $request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Task updated successfully!',
        ]);

        return to_route('teacher.classroom.task.index', $classroom);
    }

    /**
     * Remove the specified resource from storage.
     */
    #[Authorize('delete', 'task')]
    public function destroy(Classroom $classroom, Task $task)
    {
        abort_if($task->classroom_id !== $classroom->id, 404);

        if ($task->is_published) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'You cannot delete a published task!',
            ]);

            return to_route('teacher.classroom.task.index', $classroom);
        }

        if (!$this->taskService->deleteTask($task)) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' =>
                    'Task cannot be deleted because it has submissions!',
            ]);

            return to_route('teacher.classroom.task.index', $classroom);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Task deleted successfully!',
        ]);

        return to_route('teacher.classroom.task.index', $classroom);
    }

    #[Authorize('managePublishing', 'task')]
    public function publish(Classroom $classroom, Task $task)
    {
        abort_if($task->classroom_id !== $classroom->id, 404);

        if ($task->is_published) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Task is already published!',
            ]);

            return to_route('teacher.classroom.task.index', $classroom);
        }

        $this->taskService->publishTask($task);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Task published successfully!',
        ]);

        return to_route('teacher.classroom.task.index', $classroom);
    }

    #[Authorize('managePublishing', 'task')]
    public function unpublish(Classroom $classroom, Task $task)
    {
        abort_if($task->classroom_id !== $classroom->id, 404);

        if (!$task->is_published) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Task is not published!',
            ]);

            return to_route('teacher.classroom.task.index', $classroom);
        }

        if (!$this->taskService->unpublishTask($task)) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Cannot unpublish task that has submissions!',
            ]);

            return to_route('teacher.classroom.task.index', $classroom);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Task unpublished successfully!',
        ]);

        return to_route('teacher.classroom.task.index', $classroom);
    }
}
