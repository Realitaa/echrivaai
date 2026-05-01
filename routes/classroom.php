<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ClassroomController as AdminClassroomController;
use App\Http\Controllers\Teacher\ClassroomController as TeacherClassroomController;
use App\Http\Controllers\Teacher\TaskController as TeacherTaskController;
use App\Http\Controllers\Teacher\SubmissionController;
use App\Http\Controllers\Student\ClassroomController as StudentClassroomController;
use App\Http\Controllers\Student\TaskController as StudentTaskController;

Route::group(["prefix"=> "admin/classrooms", 'middleware' => ['auth', 'role:admin'], 'as' => 'admin.classroom.'], function () {
    Route::get("/", [AdminClassroomController::class, 'index'])->name("index");
    Route::delete("/{classroom}", [AdminClassroomController::class, 'destroy'])->name("destroy");
    Route::get("/{classroom}/enrollments", [AdminClassroomController::class, 'enrollments'])->name("enrollments");
});

Route::group(["prefix"=> "teacher/classrooms", 'middleware' => ['auth', 'role:teacher'], 'as' => 'teacher.classroom.'], function () {
    Route::get('/', [TeacherClassroomController::class,'index'])->name('index');
    Route::post('/', [TeacherClassroomController::class,'store'])->name('store');
    Route::delete('/{classroom}', [TeacherClassroomController::class,'destroy'])->name('destroy');
    Route::get('/{classroom}', [TeacherClassroomController::class,'show'])->name('show');
    Route::put('/{classroom}', [TeacherClassroomController::class,'update'])->name('update');

    Route::group(["prefix" => "{classroom}/tasks", "as" => "task."], function() {
        Route::get("/", [TeacherTaskController::class, "index"])->name("index");
        Route::post("/", [TeacherTaskController::class, "store"])->name("store");
        Route::get("/{task}", [TeacherTaskController::class, "show"])->name("show");
        Route::put("/{task}", [TeacherTaskController::class, "update"])->name("update");
        Route::delete("/{task}", [TeacherTaskController::class, "destroy"])->name("destroy");

        Route::group(["prefix" => "{task}/submissions", "as" => "submission."], function () {
            Route::get("/", [SubmissionController::class, "index"])->name("index");
            Route::get("/{submission}", [SubmissionController::class, "show"])->name("show");
            Route::put("/{submission}/feedback", [SubmissionController::class, "feedback"])->name("feedback");
        });
    });
});

Route::group(["prefix"=> "student/classrooms", 'middleware' => ['auth', 'role:student'], 'as' => 'student.classroom.'], function() {
    Route::get("/", [StudentClassroomController::class, "index"])->name("index");
    Route::post("/enroll", [StudentClassroomController::class, "enroll"])->name("enroll");
    Route::get("/{classroom}", [StudentClassroomController::class, "show"])->name("show");

    Route::group(["prefix"=> "{classroom}/tasks", 'as' => 'task.'], function() {
        Route::get("/", [StudentTaskController::class, "index"])->name("index");
    });
});