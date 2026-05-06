<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $dashboardService) {}

    public function index()
    {
        return Inertia::render('admin/Dashboard', [
            'stats' => $this->dashboardService->getStat(),
            'notApprovedTeacher' => $this->dashboardService->getNotApprovedTeacher(),
        ]);
    }
}
