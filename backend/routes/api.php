<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\ApplicationController;
use Illuminate\Http\Request;

// Auth Controllers
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgetPasswordController;

// Seeker Controllers
use App\Http\Controllers\Seeker\SeekerController;
use App\Http\Controllers\User\UserController;

// Provider Controllers
use App\Http\Controllers\Provider\ProfileController as ProviderProfileController;
use App\Http\Controllers\Provider\LinkController as ProviderLinkController;
use App\Http\Controllers\Provider\SkillController as ProviderSkillController;
use App\Http\Controllers\Provider\SkillRequestController as ProviderSkillRequestController;

// Admin Controllers
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SkillController as AdminSkillController;
use App\Http\Controllers\Admin\SkillRequestController as AdminSkillRequestController;

// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [ForgetPasswordController::class, 'forgotPassword']);
Route::post('/verify-reset-otp', [ForgetPasswordController::class, 'verifyResetOtp']);
Route::post('/reset-password', [ForgetPasswordController::class, 'resetPassword']);
Route::get('/listings', [ListingController::class, 'index']);

// Protected Routes (needs to be logged in)
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // User account routes (update, change password, delete account)
    Route::prefix('user')->group(function () {
        Route::get('/', [UserController::class, 'show']);
        Route::put('/', [UserController::class, 'update']);
        Route::post('/change-password', [UserController::class, 'changePassword']);
        Route::delete('/', [UserController::class, 'destroy']);
    });

    // Seeker routes
    Route::prefix('seeker')->group(function () {
        Route::get('/profile', [SeekerController::class, 'show']);
        Route::put('/profile', [SeekerController::class, 'update']);
        Route::post('/profile-picture', [SeekerController::class, 'uploadProfilePicture']);
        Route::get('/profile-picture', [SeekerController::class, 'getProfilePicture']);
        Route::delete('/profile-picture', [SeekerController::class, 'deleteProfilePicture']);
    });

    // Provider routes
    Route::middleware('role:service-provider')->prefix('provider')->group(function () {
        // Profile management
        Route::prefix('profile')->group(function () {
            Route::get('/', [ProviderProfileController::class, 'show']);
            Route::get('/stats', [ProviderProfileController::class, 'stats']);

            // Links management
           Route::prefix('links')->group(function () {
                Route::get('/', [ProviderLinkController::class, 'index']);
                Route::post('/', [ProviderLinkController::class, 'store']);
                Route::put('/{linkId}', [ProviderLinkController::class, 'update']);
                Route::delete('/{linkId}', [ProviderLinkController::class, 'destroy']);
            });
        });

        // Skills management
        Route::prefix('skills')->group(function () {
            Route::get('/', [ProviderSkillController::class, 'index']);
            Route::post('/', [ProviderSkillController::class, 'store']);
            Route::delete('/{skillId}', [ProviderSkillController::class, 'destroy']);
        });

        // Skill requests
        Route::prefix('skill-requests')->group(function () {
            Route::get('/', [ProviderSkillRequestController::class, 'index']);
            Route::post('/', [ProviderSkillRequestController::class, 'store']);
            Route::put('/{requestId}', [ProviderSkillRequestController::class, 'update']);
            Route::delete('/{requestId}', [ProviderSkillRequestController::class, 'destroy']);
            Route::get('/stats', [ProviderSkillRequestController::class, 'stats']);
        });

    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/listings/{listing}/apply', [ApplicationController::class, 'store']);
    Route::get('/listings/{listing}/applications', [ApplicationController::class, 'index']);
    Route::get('/my-applications', [ApplicationController::class, 'myApplications']);
});

// Admin routes (needs to be admin)
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    // User management
    Route::prefix('users')->group(function () {
        Route::get('/', [AdminUserController::class, 'index']);
        Route::get('/{id}', [AdminUserController::class, 'show']);
    });

    // Role management
    Route::prefix('users/{userId}/roles')->group(function () {
        Route::post('/', [RoleController::class, 'addRole']);
        Route::delete('/{role}', [RoleController::class, 'removeRole']);
    });

    // Service provider assignment
    Route::post('/users/{userId}/assign-provider', [RoleController::class, 'assignServiceProvider']);

    // Skill management
    Route::prefix('skills')->group(function () {
        Route::get('/', [AdminSkillController::class, 'index']);
        Route::post('/', [AdminSkillController::class, 'store']);
        Route::get('/search', [AdminSkillController::class, 'search']);
        Route::get('/{id}', [AdminSkillController::class, 'show']);
        Route::put('/{id}', [AdminSkillController::class, 'update']);
        Route::delete('/{id}', [AdminSkillController::class, 'destroy']);
        Route::post('/bulk-create', [AdminSkillController::class, 'bulkCreate']);
    });

    // Skill requests management
    Route::prefix('skill-requests')->group(function () {
        Route::get('/', [AdminSkillRequestController::class, 'index']);
        Route::get('/stats', [AdminSkillRequestController::class, 'stats']);
        Route::get('/{id}', [AdminSkillRequestController::class, 'show']);
        Route::post('/{id}/approve', [AdminSkillRequestController::class, 'approve']);
        Route::post('/{id}/reject', [AdminSkillRequestController::class, 'reject']);
    });
});
