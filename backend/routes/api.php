<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// ========================================================================
// AUTH CONTROLLERS
// ========================================================================
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgetPasswordController;

// ========================================================================
// USER CONTROLLERS
// ========================================================================
use App\Http\Controllers\User\UserController;

// ========================================================================
// SEEKER CONTROLLERS
// ========================================================================
use App\Http\Controllers\Seeker\SeekerController;
use App\Http\Controllers\Seeker\ListingController as SeekerListingController;
use App\Http\Controllers\Seeker\ApplicationController as SeekerApplicationController;
use App\Http\Controllers\Seeker\CategoryRequestController as SeekerCategoryRequestController;

// ========================================================================
// PROVIDER CONTROLLERS
// ========================================================================
use App\Http\Controllers\Provider\ApplicationController as ProviderApplicationController;
use App\Http\Controllers\Provider\ProfileController as ProviderProfileController;
use App\Http\Controllers\Provider\LinkController as ProviderLinkController;
use App\Http\Controllers\Provider\SkillController as ProviderSkillController;
use App\Http\Controllers\Provider\SkillRequestController as ProviderSkillRequestController;
use App\Http\Controllers\Provider\ServiceController as ProviderServiceController;
use App\Http\Controllers\Provider\PublicProviderController;

// ========================================================================
// PUBLIC LISTING CONTROLLER
// ========================================================================
use App\Http\Controllers\Listing\ListingController as PublicListingController;

// ========================================================================
// CATEGORY CONTROLLERS
// ========================================================================
use App\Http\Controllers\Category\CategoryController as PublicCategoryController;

// ========================================================================
// ADMIN CONTROLLERS
// ========================================================================
use App\Http\Controllers\Admin\ListingController as AdminListingController;
use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CategoryRequestController as AdminCategoryRequestController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SkillController as AdminSkillController;
use App\Http\Controllers\Admin\SkillRequestController as AdminSkillRequestController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ========================================================================
// PUBLIC ROUTES (No Authentication Required)
// ========================================================================

// Auth Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [ForgetPasswordController::class, 'forgotPassword']);
Route::post('/verify-reset-otp', [ForgetPasswordController::class, 'verifyResetOtp']);
Route::post('/reset-password', [ForgetPasswordController::class, 'resetPassword']);

// Public Listing Routes
Route::get('/listings', [PublicListingController::class, 'index']);
Route::get('/listings/{id}', [PublicListingController::class, 'show']);

// Public Category Routes
Route::get('/categories', [PublicCategoryController::class, 'index']);
Route::get('/categories/{id}', [PublicCategoryController::class, 'show']);

// Public Provider Routes
Route::get('/providers', [PublicProviderController::class, 'index']);
Route::get('/providers/{id}', [PublicProviderController::class, 'show']);

// ========================================================================
// PROTECTED ROUTES (Requires Authentication)
// ========================================================================
Route::middleware('auth:sanctum')->group(function () {

    // ====================================================================
    // AUTH ROUTES
    // ====================================================================
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // ====================================================================
    // USER ACCOUNT ROUTES
    // ====================================================================
    Route::prefix('user')->group(function () {
        Route::get('/', [UserController::class, 'show']);
        Route::put('/', [UserController::class, 'update']);
        Route::post('/change-password', [UserController::class, 'changePassword']);
        Route::delete('/', [UserController::class, 'destroy']);
    });

    // ====================================================================
    // SEEKER ROUTES
    // ====================================================================
    Route::prefix('seeker')->group(function () {

        // Seeker Profile Routes
        Route::get('/profile', [SeekerController::class, 'show']);
        Route::put('/profile', [SeekerController::class, 'update']);
        Route::post('/profile-picture', [SeekerController::class, 'uploadProfilePicture']);
        Route::get('/profile-picture', [SeekerController::class, 'getProfilePicture']);
        Route::delete('/profile-picture', [SeekerController::class, 'deleteProfilePicture']);

        // Seeker Listing Routes
Route::prefix('listings')->group(function () {
    // List and Search
    Route::get('/', [SeekerListingController::class, 'index']);

    // CRUD Operations
    Route::get('/{id}', [SeekerListingController::class, 'show']);
    Route::post('/', [SeekerListingController::class, 'store']);
    Route::put('/{id}', [SeekerListingController::class, 'update']);
    Route::patch('/{id}', [SeekerListingController::class, 'update']);
    Route::delete('/{id}', [SeekerListingController::class, 'destroy']);

    // Tag Management
    Route::post('/{id}/tags', [SeekerListingController::class, 'addTag']);
    Route::delete('/{id}/tags', [SeekerListingController::class, 'removeTag']);

    // Applications for specific listing
    Route::get('/{listingId}/applications', [SeekerApplicationController::class, 'index']);
    Route::get('/{listingId}/applications/{applicationId}', [SeekerApplicationController::class, 'show']);
    Route::post('/{listingId}/applications/{applicationId}/accept', [SeekerApplicationController::class, 'accept']);
    Route::post('/{listingId}/applications/{applicationId}/reject', [SeekerApplicationController::class, 'reject']);
});

        // Seeker Category Request Routes
        Route::prefix('category-requests')->group(function () {
            Route::get('/', [SeekerCategoryRequestController::class, 'index']);
            Route::get('/{id}', [SeekerCategoryRequestController::class, 'show']);
            Route::post('/', [SeekerCategoryRequestController::class, 'store']);
        });
    });

    // ====================================================================
    // PROVIDER ROUTES
    // ====================================================================
    Route::middleware('role:service-provider')->prefix('provider')->group(function () {

        // Provider Profile Routes
        Route::prefix('profile')->group(function () {
            Route::get('/', [ProviderProfileController::class, 'show']);
            Route::get('/stats', [ProviderProfileController::class, 'stats']);
        });

        // Provider Service Routes
        Route::apiResource('services', ProviderServiceController::class);
        Route::post('services/{service}/tags', [ProviderServiceController::class, 'addTags']);
        Route::delete('services/{service}/tags', [ProviderServiceController::class, 'removeTags']);

        // Provider Link Routes
        Route::prefix('links')->group(function () {
            Route::get('/', [ProviderLinkController::class, 'index']);
            Route::post('/', [ProviderLinkController::class, 'store']);
            Route::put('/{linkId}', [ProviderLinkController::class, 'update']);
            Route::delete('/{linkId}', [ProviderLinkController::class, 'destroy']);
        });

        // Provider Skill Routes
        Route::prefix('skills')->group(function () {
            Route::get('/', [ProviderSkillController::class, 'index']);
            Route::post('/', [ProviderSkillController::class, 'store']);
            Route::delete('/{skillId}', [ProviderSkillController::class, 'destroy']);
        });

        // Provider Skill Request Routes
        Route::prefix('skill-requests')->group(function () {
            Route::get('/', [ProviderSkillRequestController::class, 'index']);
            Route::get('/stats', [ProviderSkillRequestController::class, 'stats']);
            Route::post('/', [ProviderSkillRequestController::class, 'store']);
            Route::put('/{requestId}', [ProviderSkillRequestController::class, 'update']);
            Route::delete('/{requestId}', [ProviderSkillRequestController::class, 'destroy']);
        });

        // Provider Application Routes
        Route::post('/listings/{listingId}/applications', [ProviderApplicationController::class, 'store']);
        Route::prefix('applications')->group(function () {
            Route::get('/', [ProviderApplicationController::class, 'index']);
            Route::get('/{applicationId}', [ProviderApplicationController::class, 'show']);
            Route::patch('/{applicationId}', [ProviderApplicationController::class, 'update']);
            Route::delete('/{applicationId}', [ProviderApplicationController::class, 'destroy']);
        });
    });
});

// ========================================================================
// ADMIN ROUTES (Requires Authentication + Admin Role)
// ========================================================================
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {

    // ====================================================================
    // ADMIN LISTING ROUTES
    // ====================================================================
    Route::prefix('listings')->group(function () {
        Route::get('/', [AdminListingController::class, 'index']);
        Route::get('/stats', [AdminListingController::class, 'stats']);
        Route::get('/deleted', [AdminListingController::class, 'getDeleted']);
        Route::get('/{listingId}', [AdminListingController::class, 'show']);
        Route::delete('/{listingId}', [AdminListingController::class, 'destroy']);
        Route::delete('/{listingId}/force', [AdminListingController::class, 'forceDelete']);
        Route::post('/{listingId}/restore', [AdminListingController::class, 'restore']);
    });

    // ====================================================================
    // ADMIN SERVICE ROUTES
    // ====================================================================
    Route::prefix('services')->group(function () {
        Route::get('/', [AdminServiceController::class, 'index']);
        Route::get('/{service}', [AdminServiceController::class, 'show']);
        Route::delete('/{service}', [AdminServiceController::class, 'destroy']);
        Route::post('/{service}/suspend', [AdminServiceController::class, 'suspend']);
        Route::post('/{service}/reactivate', [AdminServiceController::class, 'reactivate']);
    });

    // ====================================================================
    // ADMIN APPLICATION ROUTES
    // ====================================================================
    Route::prefix('applications')->group(function () {
        Route::get('/', [AdminApplicationController::class, 'index']);
        Route::get('/stats', [AdminApplicationController::class, 'stats']);
        Route::get('/{applicationId}', [AdminApplicationController::class, 'show']);
        Route::delete('/{applicationId}', [AdminApplicationController::class, 'destroy']);
    });

    // ====================================================================
    // ADMIN CATEGORY ROUTES
    // ====================================================================
    Route::prefix('categories')->group(function () {
        Route::get('/', [AdminCategoryController::class, 'index']);
        Route::get('/stats', [AdminCategoryController::class, 'stats']);
        Route::get('/deleted', [AdminCategoryController::class, 'getDeleted']);
        Route::get('/{id}', [AdminCategoryController::class, 'show']);
        Route::post('/', [AdminCategoryController::class, 'store']);
        Route::patch('/{id}', [AdminCategoryController::class, 'update']);
        Route::delete('/{id}', [AdminCategoryController::class, 'destroy']);
        Route::delete('/{id}/force', [AdminCategoryController::class, 'forceDelete']);
        Route::post('/{id}/restore', [AdminCategoryController::class, 'restore']);
    });

    // ====================================================================
    // ADMIN CATEGORY REQUEST ROUTES
    // ====================================================================
    Route::prefix('category-requests')->group(function () {
        Route::get('/', [AdminCategoryRequestController::class, 'index']);
        Route::get('/stats', [AdminCategoryRequestController::class, 'stats']);
        Route::get('/{id}', [AdminCategoryRequestController::class, 'show']);
        Route::post('/{id}/approve', [AdminCategoryRequestController::class, 'approve']);
        Route::post('/{id}/reject', [AdminCategoryRequestController::class, 'reject']);
        Route::delete('/{id}', [AdminCategoryRequestController::class, 'destroy']);
    });

    // ====================================================================
    // ADMIN TAG ROUTES
    // ====================================================================
    Route::prefix('tags')->group(function () {
        Route::get('/', [TagController::class, 'index']);
        Route::get('/{id}', [TagController::class, 'show']);
        Route::post('/', [TagController::class, 'store']);
        Route::patch('/{id}', [TagController::class, 'update']);
        Route::delete('/{id}', [TagController::class, 'destroy']);
        Route::post('/{id}/restore', [TagController::class, 'restore']);
    });

    // ====================================================================
    // ADMIN USER ROUTES
    // ====================================================================
    Route::prefix('users')->group(function () {
        Route::get('/', [AdminUserController::class, 'index']);
        Route::get('/{id}', [AdminUserController::class, 'show']);

        // Role Management Routes
        Route::prefix('{userId}/roles')->group(function () {
            Route::post('/', [RoleController::class, 'addRole']);
            Route::delete('/{role}', [RoleController::class, 'removeRole']);
        });

        // Service Provider Assignment
        Route::post('/{userId}/assign-provider', [RoleController::class, 'assignServiceProvider']);
    });

    // ====================================================================
    // ADMIN SKILL ROUTES
    // ====================================================================
    Route::prefix('skills')->group(function () {
        Route::get('/', [AdminSkillController::class, 'index']);
        Route::get('/search', [AdminSkillController::class, 'search']);
        Route::get('/{id}', [AdminSkillController::class, 'show']);
        Route::post('/', [AdminSkillController::class, 'store']);
        Route::post('/bulk-create', [AdminSkillController::class, 'bulkCreate']);
        Route::put('/{id}', [AdminSkillController::class, 'update']);
        Route::delete('/{id}', [AdminSkillController::class, 'destroy']);
    });

    // ====================================================================
    // ADMIN SKILL REQUEST ROUTES
    // ====================================================================
    Route::prefix('skill-requests')->group(function () {
        Route::get('/', [AdminSkillRequestController::class, 'index']);
        Route::get('/stats', [AdminSkillRequestController::class, 'stats']);
        Route::get('/{id}', [AdminSkillRequestController::class, 'show']);
        Route::post('/{id}/approve', [AdminSkillRequestController::class, 'approve']);
        Route::post('/{id}/reject', [AdminSkillRequestController::class, 'reject']);
    });
});
