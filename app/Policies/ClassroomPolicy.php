<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Classroom;

class ClassroomPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function view(User $user, Classroom $classroom): bool
    {
        return $user->is($classroom->teacher);
    }

    public function viewAsStudent(User $user, Classroom $classroom): bool
    {
        return $classroom->enrollments()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, Classroom $classroom): bool
    {
        return $user->is($classroom->teacher);
    }

    public function delete(User $user, Classroom $classroom): bool
    {
        return $user->is($classroom->teacher);
    }
}
