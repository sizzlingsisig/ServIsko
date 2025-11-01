<?php

use App\Http\Controllers\ForgetPasswordController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SeekerController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SkillController;


//Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [ForgetPasswordController::class, 'forgotPassword']);
Route::post('/verify-reset-otp', [ForgetPasswordController::class, 'verifyResetOtp']);
Route::post('/reset-password', [ForgetPasswordController::class, 'resetPassword']);


//Protected Routes (needs to be logged in)
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
});

//Admin routes (needs to be admin)
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
});
