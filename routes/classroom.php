<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ClassroomController;

Route::group(["prefix"=> "admin/classrooms", 'middleware' => 'role:admin', 'as' => 'admin.classroom.'], function () {
    Route::get("/", [ClassroomController::class, 'index'])->name("index");
    Route::delete("/{classroom}", [ClassroomController::class, 'destroy'])->name("destroy");
    Route::get("/{classroom}/enrollments", [ClassroomController::class, 'enrollments'])->name("enrollments");
});