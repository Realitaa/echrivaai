<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth'])->group(function () {
    Route::inertia('admin/dashboard', 'admin/Dashboard')->name('dashboard')->middleware('role:admin');
    Route::inertia('register/pending', 'auth/RegisterPending')->name('register.pending')->middleware('role:teacher');

    require __DIR__ . '/settings.php';
    require __DIR__ . '/users.php';
    require __DIR__ . '/classroom.php';
    require __DIR__ . '/tasks.php';
    require __DIR__ . '/submission.php';
    require __DIR__ . '/files.php';
});
