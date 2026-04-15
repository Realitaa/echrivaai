<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::group(["prefix"=> "users", 'middleware' => 'role:admin'], function () {
    Route::get("/", [UserController::class, 'index'])->name("admin.users.index");
    Route::post("/", [UserController::class, 'store'])->name("admin.users.store");
    Route::put("/{user}", [UserController::class, 'update'])->name("admin.users.update");
    Route::delete("/{user}", [UserController::class, 'destroy'])->name("admin.users.destroy");
    Route::patch("/{user}/approve", [UserController::class, 'approve'])->name("admin.users.approve");
});