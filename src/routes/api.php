<?php

use Illuminate\Support\Facades\Route;


Route::get("api/v1/media", [Usermp\LaravelMedia\Controllers\MediaController::class,'index'])->name("media");
Route::post("api/v1/media", [Usermp\LaravelMedia\Controllers\MediaController::class,'upload'])->name("upload-media");
Route::post("api/v1/directory", [Usermp\LaravelMedia\Controllers\MediaController::class,'directory'])->name("directory");
