<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TaskController;

Route::group(["prefix"=> "admin/tasks", 'middleware' => 'role:admin', 'as' => 'admin.task.'], function () {
    Route::get("/", [TaskController::class, 'index'])->name("index");
    Route::delete("/{task}", [TaskController::class, 'destroy'])->name("destroy");
});