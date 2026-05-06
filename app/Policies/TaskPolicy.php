<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        return $user->is($task->classroom->teacher);
    }

    public function viewAsStudent(User $user, Task $task): bool
    {
        return $task->is_published && 
               $task->classroom->enrollments()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, Task $task): bool
    {
        return $user->is($task->classroom->teacher);
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->is($task->classroom->teacher);
    }
}
