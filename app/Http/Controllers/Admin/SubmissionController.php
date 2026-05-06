<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Submission;
use App\Services\Admin\SubmissionService;
use Inertia\Inertia;
use Illuminate\Routing\Attributes\Controllers\Authorize;

class SubmissionController extends Controller
{
    public function __construct(
        protected SubmissionService $submissionService,
    ) {}

    #[Authorize('viewAny', Submission::class)]
    public function index(Request $request)
    {
        $submissions = $this->submissionService->getPaginatedSubmissions(
            $request,
        );

        return Inertia::render('admin/submission/Index', [
            'submissions' => $submissions,
        ]);
    }

    #[Authorize('view', 'submission')]
    public function show(Submission $submission)
    {
        // Load semua yang dibutuhkan sesuai model kamu
        $submission = $this->submissionService->loadSubmissionDetails(
            $submission,
        );

        return Inertia::render('admin/submission/Detail', [
            'submission' => $submission,
        ]);
    }
}
