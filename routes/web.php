<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\UserController;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('admin/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard')
        ->middleware('role:admin');
    Route::inertia('register/pending', 'auth/RegisterPending')
        ->name('register.pending')
        ->middleware('role:teacher');
    Route::patch('locale', [UserController::class, 'updateLocale'])
        ->name('locale.update');

    require __DIR__ . '/settings.php';
    require __DIR__ . '/users.php';
    require __DIR__ . '/classroom.php';
    require __DIR__ . '/tasks.php';
    require __DIR__ . '/submission.php';
    require __DIR__ . '/files.php';
});
