<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SubmissionController;

Route::group(["prefix"=> "admin/submissions", 'middleware' => 'role:admin', 'as' => 'admin.submission.'], function () {
    Route::get("/", [SubmissionController::class, 'index'])->name("index");
    Route::get("/{submission}", [SubmissionController::class, 'show'])->name("show");
});