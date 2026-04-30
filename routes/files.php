<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;

Route::group(["prefix" => "/files", "as" => "file."], function () {
    Route::post("/upload", [FileController::class, "upload"])
    ->name("upload");
    Route::delete("/remove/{file}", [FileController::class,"remove"])
    ->name("remove");
});