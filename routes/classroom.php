<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ClassroomController as AdminClassroomController;
use App\Http\Controllers\Teacher\ClassroomController as TeacherClassroomController;
use App\Http\Controllers\Teacher\TaskController;

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
        Route::get("/", [TaskController::class, "index"])->name("index");
        Route::post("/", [TaskController::class, "store"])->name("store");
        Route::get("/{task}", [TaskController::class, "show"])->name("show");
        Route::put("/{task}", [TaskController::class, "update"])->name("update");
        Route::delete("/{task}", [TaskController::class, "destroy"])->name("destroy");
    });
});