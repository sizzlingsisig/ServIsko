<?php

namespace App\Http\Controllers\Seeker;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seeker\CategoryRequest\StoreCategoryRequestRequest;
use App\Http\Requests\Seeker\CategoryRequest\FilterCategoryRequestRequest;
use App\Services\Seeker\CategoryRequestService;
use Illuminate\Support\Facades\Log;
use Exception;

class CategoryRequestController extends Controller
{
    protected $categoryRequestService;

    public function __construct(CategoryRequestService $categoryRequestService)
    {
        $this->categoryRequestService = $categoryRequestService;
    }

    /**
     * Create category request
     * POST /seeker/category-requests
     */
    public function store(StoreCategoryRequestRequest $request)
    {
        try {
            $categoryRequest = $this->categoryRequestService->createCategoryRequest(
                auth()->id(),
                $request->validated()
            );

            Log::info('Category request created', [
                'request_id' => $categoryRequest->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category request submitted. Admin will review shortly.',
                'data' => $categoryRequest,
            ], 201);
        } catch (Exception $e) {
            Log::error('Failed to create category request: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit category request.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get user's category requests with optional filters
     * GET /seeker/category-requests?status=pending&per_page=15
     */
    public function index(FilterCategoryRequestRequest $request)
    {
        try {
            $requests = $this->categoryRequestService->getUserCategoryRequests(
                auth()->id(),
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'data' => $requests,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch category requests: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch requests.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get specific category request
     * GET /seeker/category-requests/{id}
     */
    public function show($id)
    {
        try {
            $categoryRequest = $this->categoryRequestService->getUserCategoryRequest(
                auth()->id(),
                $id
            );

            return response()->json([
                'success' => true,
                'data' => $categoryRequest,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch category request: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 404);
        }
    }
}
