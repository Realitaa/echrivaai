<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Submission;
use App\Services\Admin\SubmissionService;
use Inertia\Inertia;

class SubmissionController extends Controller
{
    public function __construct(
        protected SubmissionService $submissionService,
    ) {}

    public function index(Request $request)
    {
        // Authorization (simple & consistent)
        abort_unless(auth()->user()->role === 'admin', 403);

        $submissions = $this->submissionService->getPaginatedSubmissions(
            $request,
        );

        return Inertia::render('admin/submission/Index', [
            'submissions' => $submissions,
        ]);
    }

    public function show(Submission $submission)
    {
        abort_unless(auth()->user()->role === 'admin', 403);

        // Load semua yang dibutuhkan sesuai model kamu
        $submission = $this->submissionService->loadSubmissionDetails(
            $submission,
        );

        return Inertia::render('admin/submission/Detail', [
            'submission' => $submission,
        ]);
    }
}
