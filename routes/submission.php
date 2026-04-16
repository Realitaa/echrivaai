<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SubmissionController;

Route::group(["prefix"=> "submission", 'middleware' => 'role:admin'], function () {
    Route::get("/", [SubmissionController::class, 'index'])->name("admin.submission.index");
    Route::get("/{submission}", [SubmissionController::class, 'show'])->name("admin.submission.show");
});