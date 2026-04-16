<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ClassroomController;

Route::group(["prefix"=> "classroom", 'middleware' => 'role:admin'], function () {
    Route::get("/", [ClassroomController::class, 'index'])->name("admin.classroom.index");
    Route::delete("/{classroom}", [ClassroomController::class, 'destroy'])->name("admin.classroom.destroy");
    Route::get("/{classroom}/enrollments", [ClassroomController::class, 'enrollments'])->name("admin.classroom.enrollments");
});