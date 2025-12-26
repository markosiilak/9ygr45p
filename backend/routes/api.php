<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\TranslationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authentication routes with strict rate limiting (5 attempts per minute per IP)
Route::middleware('throttle:auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Public routes - anyone can view events
Route::get('events', [EventController::class, 'index']);
Route::get('events/{id}', [EventController::class, 'show']);

// Translation routes - public, cached
Route::get('translations/{locale}', [TranslationController::class, 'index'])->where('locale', 'en|et');
Route::get('translations', [TranslationController::class, 'locales']);

// Protected routes - require authentication
Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [AuthController::class, 'user']);
    Route::post('logout', [AuthController::class, 'logout']);

    // User management routes - require manage-users permission (admin only)
    Route::get('users', [AuthController::class, 'index']);
    Route::get('roles', [AuthController::class, 'getRoles']);
    Route::put('users/{userId}/roles', [AuthController::class, 'setRoles']);
});
