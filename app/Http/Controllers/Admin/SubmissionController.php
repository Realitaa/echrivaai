<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Submission;
use Inertia\Inertia;

class SubmissionController extends Controller
{
    public function index(Request $request)
    {
        // Authorization (simple & consistent)
        abort_unless(auth()->user()->role === 'admin', 403);

        $query = Submission::query()
            ->with([
                'user:id,name',
                'task:id,title',
            ])
            ->latest();

        // (optional future filter)
        if ($request->filled('task_id')) {
            $query->where('task_id', $request->task_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $submissions = $query->paginate(10)->withQueryString();

        return Inertia::render('admin/submission/Index', [
            'submissions' => $submissions,
        ]);
    }

    public function show(Submission $submission)
    {
        abort_unless(auth()->user()->role === 'admin', 403);

        // Load semua yang dibutuhkan sesuai model kamu
        $submission->load([
            'user:id,name,email',
            'task:id,title',
            'aiFeedbacks',
            'files',
        ]);

        return Inertia::render('admin/submission/Detail', [
            'submission' => $submission,
        ]);
    }
}
