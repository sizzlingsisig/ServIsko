<?php

use App\Http\Controllers\ForgetPasswordController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SeekerController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\SkillRequestController;
use App\Http\Controllers\Admin\SkillRequestController as AdminSkillRequestController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SkillController;

// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [ForgetPasswordController::class, 'forgotPassword']);
Route::post('/verify-reset-otp', [ForgetPasswordController::class, 'verifyResetOtp']);
Route::post('/reset-password', [ForgetPasswordController::class, 'resetPassword']);

// Protected Routes (needs to be logged in)
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Seeker routes
    Route::prefix('seeker')->group(function () {
        Route::get('/profile', [SeekerController::class, 'show']);
        Route::put('/profile', [SeekerController::class, 'updateProfile']);
        Route::post('/password', [SeekerController::class, 'changePassword']);
        Route::post('/profile-picture', [SeekerController::class, 'uploadProfilePicture']);
        Route::get('/profile-picture', [SeekerController::class, 'getProfilePicture']);
        Route::delete('/', [SeekerController::class, 'destroy']);
    });

    // Provider routes
    Route::middleware('role:service-provider')->prefix('provider')->group(function () {
        // Profile management
        Route::prefix('profile')->group(function () {
            Route::get('/', [ProviderController::class, 'show']);
            Route::put('/', [ProviderController::class, 'updateProfile']);

            // Links management
            Route::prefix('links')->group(function () {
                Route::post('/', [ProviderController::class, 'addLink']);
                Route::put('/{linkId}', [ProviderController::class, 'updateLink']);
                Route::delete('/{linkId}', [ProviderController::class, 'removeLink']);
            });
        });

        // Skills management
        Route::prefix('skills')->group(function () {
            Route::get('/', [ProviderController::class, 'getSkills']);
            Route::get('/search', [ProviderController::class, 'searchSkills']);
            Route::post('/', [ProviderController::class, 'addSkill']);
            Route::put('/{skillId}', [ProviderController::class, 'updateSkill']);
            Route::delete('/{skillId}', [ProviderController::class, 'removeSkill']);
        });

        // Skill requests
        Route::prefix('skill-requests')->group(function () {
            Route::post('/', [ProviderController::class, 'submitSkillRequest']);
            Route::get('/', [ProviderController::class, 'getMySkillRequests']);
        });
    });


});

// Admin routes (needs to be admin)
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    // User management
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::post('/{userId}/add-role', [UserController::class, 'addRole']);
        Route::post('/{userId}/become-provider', [UserController::class, 'assignServiceProvider']);
    });

    // Skill management
    Route::prefix('skills')->group(function () {
        Route::get('/', [SkillController::class, 'index']);
        Route::post('/', [SkillController::class, 'store']);
        Route::post('/bulk-create', [SkillController::class, 'bulkCreate']);
        Route::get('/search', [SkillController::class, 'search']);
        Route::get('/{id}', [SkillController::class, 'show']);
        Route::put('/{id}', [SkillController::class, 'update']);
        Route::delete('/{id}', [SkillController::class, 'destroy']);
    });

    // // Skill requests management
    // Route::prefix('skill-requests')->group(function () {
    //     Route::get('/', [AdminSkillRequestController::class, 'index']);
    //     Route::get('/{id}', [AdminSkillRequestController::class, 'show']);
    //     Route::post('/{id}/approve', [AdminSkillRequestController::class, 'approve']);
    //     Route::post('/{id}/reject', [AdminSkillRequestController::class, 'reject']);
    //     Route::get('/stats', [AdminSkillRequestController::class, 'stats']);
    // });
});
