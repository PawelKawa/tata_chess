<?php

use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\PostController;
use Illuminate\Support\Facades\Route;

Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{slug}', [PostController::class, 'show']);
Route::post('/contact', [ContactController::class, 'send']);
