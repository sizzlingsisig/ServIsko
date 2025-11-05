<?php

use App\Http\Controllers\ForgetPasswordController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\ApplicationController;

// Public routes (no authentication required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [ForgetPasswordController::class, 'forgotPassword']);
Route::post('/verify-reset-otp', [ForgetPasswordController::class, 'verifyResetOtp']);
Route::post('/reset-password', [ForgetPasswordController::class, 'resetPassword']);
Route::get('/listings', [ListingController::class, 'index']);


// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/listings/{listing}/apply', [ApplicationController::class, 'store']);
    Route::get('/listings/{listing}/applications', [ApplicationController::class, 'index']);
    Route::get('/my-applications', [ApplicationController::class, 'myApplications']);
});

