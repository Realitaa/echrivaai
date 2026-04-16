<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::group(["prefix"=> "admin/users", 'middleware' => 'role:admin', 'as' => 'admin.user.'], function () {
    Route::get("/", [UserController::class, 'index'])->name("index");
    Route::post("/", [UserController::class, 'store'])->name("store");
    Route::put("/{user}", [UserController::class, 'update'])->name("update");
    Route::delete("/{user}", [UserController::class, 'destroy'])->name("destroy");
    Route::patch("/{user}/approve", [UserController::class, 'approve'])->name("approve");
});