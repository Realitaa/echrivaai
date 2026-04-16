<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TaskController;

Route::group(["prefix"=> "tasks", 'middleware' => 'role:admin'], function () {
    Route::get("/", [TaskController::class, 'index'])->name("admin.tasks.index");
    Route::delete("/{task}", [TaskController::class, 'destroy'])->name("admin.tasks.destroy");
});