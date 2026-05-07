<?php

namespace App\Policies;

use App\Models\Submission;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function view(User $user, Submission $submission): bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        return $user->is($submission->task->classroom->teacher);
    }

    public function viewAsStudent(User $user, Submission $submission): bool
    {
        return $user->is($submission->user) &&
            $submission->task->classroom
                ->enrollments()
                ->where('user_id', $user->id)
                ->exists();
    }

    public function update(User $user, Submission $submission): bool
    {
        return $user->is($submission->task->classroom->teacher);
    }
}
