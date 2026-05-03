<?php

namespace App\Services\Admin;

use App\Models\Submission;
use Illuminate\Http\Request;

class SubmissionService
{
    public function getPaginatedSubmissions(Request $request)
    {
        $query = Submission::query()
            ->with(['user:id,name', 'task:id,title'])
            ->latest();

        if ($request->filled('task_id')) {
            $query->where('task_id', $request->task_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        return $query->paginate(10)->withQueryString();
    }

    public function loadSubmissionDetails(Submission $submission): Submission
    {
        return $submission->load([
            'user:id,name,email',
            'task:id,title',
            'aiFeedbacks',
            'files',
        ]);
    }
}
