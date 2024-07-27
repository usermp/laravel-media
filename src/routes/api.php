<?php

use Illuminate\Support\Facades\Route;


Route::get("api/v1/media", [MyVendor\LaravelMedia\Controllers\MediaController::class,'index'])->name("media");
Route::post("api/v1/media", [MyVendor\LaravelMedia\Controllers\MediaController::class,'upload'])->name("upload-media");
