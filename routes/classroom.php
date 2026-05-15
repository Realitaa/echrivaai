<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ClassroomController as AdminClassroomController;
use App\Http\Controllers\Teacher\ClassroomController as TeacherClassroomController;
use App\Http\Controllers\Teacher\TaskController as TeacherTaskController;
use App\Http\Controllers\Teacher\SubmissionController as TeacherSubmissionController;
use App\Http\Controllers\Student\ClassroomController as StudentClassroomController;
use App\Http\Controllers\Student\TaskController as StudentTaskController;
use App\Http\Controllers\Student\SubmissionController as StudentSubmissionController;

Route::group(
    [
        'prefix' => 'admin/classrooms',
        'middleware' => ['auth', 'role:admin'],
        'as' => 'admin.classroom.',
    ],
    function () {
        Route::get('/', [AdminClassroomController::class, 'index'])->name(
            'index',
        );
        Route::delete('/{classroom}', [
            AdminClassroomController::class,
            'destroy',
        ])->name('destroy');
        Route::get('/{classroom}/enrollments', [
            AdminClassroomController::class,
            'enrollments',
        ])->name('enrollments');
    },
);

Route::group(
    [
        'prefix' => 'teacher/classrooms',
        'middleware' => ['auth', 'role:teacher'],
        'as' => 'teacher.classroom.',
    ],
    function () {
        Route::get('/', [TeacherClassroomController::class, 'index'])->name(
            'index',
        );
        Route::post('/', [TeacherClassroomController::class, 'store'])->name(
            'store',
        );
        Route::delete('/{classroom}', [
            TeacherClassroomController::class,
            'destroy',
        ])->name('destroy');
        Route::get('/{classroom}', [
            TeacherClassroomController::class,
            'show',
        ])->name('show');
        Route::put('/{classroom}', [
            TeacherClassroomController::class,
            'update',
        ])->name('update');

        Route::group(
            ['prefix' => '{classroom}/tasks', 'as' => 'task.'],
            function () {
                Route::get('/', [TeacherTaskController::class, 'index'])->name(
                    'index',
                );
                Route::get('/create', [
                    TeacherTaskController::class,
                    'create',
                ])->name('create');
                Route::post('/', [TeacherTaskController::class, 'store'])->name(
                    'store',
                );
                Route::get('/{task}', [
                    TeacherTaskController::class,
                    'show',
                ])->name('show');
                Route::get('/{task}/edit', [
                    TeacherTaskController::class,
                    'edit',
                ])->name('edit');
                Route::put('/{task}', [
                    TeacherTaskController::class,
                    'update',
                ])->name('update');
                Route::delete('/{task}', [
                    TeacherTaskController::class,
                    'destroy',
                ])->name('destroy');
                Route::patch('/{task}/publish', [
                    TeacherTaskController::class,
                    'publish',
                ])->name('publish');
                Route::patch('/{task}/unpublish', [
                    TeacherTaskController::class,
                    'unpublish',
                ])->name('unpublish');

                Route::group(
                    ['prefix' => '{task}/submissions', 'as' => 'submission.'],
                    function () {
                        Route::get('/', [
                            TeacherSubmissionController::class,
                            'index',
                        ])->name('index');
                        Route::get('/{submission}', [
                            TeacherSubmissionController::class,
                            'show',
                        ])->name('show');
                        Route::get('/history/{student}', [
                            TeacherSubmissionController::class,
                            'history',
                        ])->name('history');
                        Route::put('/{submission}/feedback', [
                            TeacherSubmissionController::class,
                            'feedback',
                        ])->name('feedback');
                    },
                );
            },
        );
    },
);

Route::group(
    [
        'prefix' => 'student/classrooms',
        'middleware' => ['auth', 'role:student'],
        'as' => 'student.classroom.',
    ],
    function () {
        Route::get('/', [StudentClassroomController::class, 'index'])->name(
            'index',
        );
        Route::post('/enroll', [
            StudentClassroomController::class,
            'enroll',
        ])->name('enroll');
        Route::get('/{classroom}', [
            StudentClassroomController::class,
            'show',
        ])->name('show');

        Route::group(
            ['prefix' => '{classroom}/tasks', 'as' => 'task.'],
            function () {
                Route::get('/', [StudentTaskController::class, 'index'])->name(
                    'index',
                );
                Route::get('/{task}', [
                    StudentTaskController::class,
                    'show',
                ])->name('show');

                Route::group(
                    ['prefix' => '{task}/submissions', 'as' => 'submission.'],
                    function () {
                        Route::get('/{submission}', [
                            StudentSubmissionController::class,
                            'show',
                        ])->name('show');
                        Route::post('/', [
                            StudentSubmissionController::class,
                            'store',
                        ])->name('store');
                    },
                );
            },
        );
    },
);
