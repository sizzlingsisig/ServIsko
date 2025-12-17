<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest\FilterCategoryRequestRequest;
use App\Http\Requests\Admin\CategoryRequest\RejectCategoryRequestRequest;
use App\Services\Admin\CategoryRequestService;
use Illuminate\Support\Facades\DB;
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
     * Get all category requests with filters (admin only)
     * GET /admin/category-requests?status=pending&user_id=5&per_page=20
     */
    public function index(FilterCategoryRequestRequest $request)
    {
        try {
            $requests = $this->categoryRequestService->getCategoryRequests(
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
     * Get specific category request (admin only)
     * GET /admin/category-requests/{id}
     */
    public function show($id)
    {
        try {
            $categoryRequest = $this->categoryRequestService->getCategoryRequest($id);

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

    /**
     * Get category request statistics (admin only)
     * GET /admin/category-requests/stats
     */
    public function stats()
    {
        try {
            $stats = $this->categoryRequestService->getStats();

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch category request stats: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Approve category request and create category (admin only)
     * POST /admin/category-requests/{id}/approve
     */
    public function approve($id)
    {
        try {
            $categoryRequest = DB::transaction(function () use ($id) {
                return $this->categoryRequestService->approveCategoryRequest($id);
            });

            Log::info('Category request approved', [
                'request_id' => $categoryRequest->id,
                'admin_id' => auth()->id(),
                'category_name' => $categoryRequest->name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category request approved. New category created.',
                'data' => $categoryRequest,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to approve category request: ' . $e->getMessage());

            $statusCode = str_contains($e->getMessage(), 'not found') ? 404 : 409;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], $statusCode);
        }
    }

    /**
     * Reject category request (admin only)
     * POST /admin/category-requests/{id}/reject
     */
    public function reject(RejectCategoryRequestRequest $request, $id)
    {
        try {
            $categoryRequest = $this->categoryRequestService->rejectCategoryRequest(
                $id,
                $request->input('admin_notes')
            );

            Log::info('Category request rejected', [
                'request_id' => $id,
                'admin_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category request rejected.',
                'data' => $categoryRequest,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to reject category request: ' . $e->getMessage());

            $statusCode = str_contains($e->getMessage(), 'not found') ? 404 : 409;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], $statusCode);
        }
    }

    /**
     * Delete category request (admin only)
     * DELETE /admin/category-requests/{id}
     */
    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $this->categoryRequestService->deleteCategoryRequest($id);
            });

            Log::info('Category request deleted', [
                'request_id' => $id,
                'admin_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category request deleted.',
            ]);
        } catch (Exception $e) {
            Log::error('Failed to delete category request: ' . $e->getMessage());

            $statusCode = str_contains($e->getMessage(), 'not found') ? 404 : 500;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], $statusCode);
        }
    }
}
