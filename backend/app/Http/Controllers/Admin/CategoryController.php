<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Category\FilterCategoryRequest;
use App\Http\Requests\Admin\Category\StoreCategoryRequest;
use App\Http\Requests\Admin\Category\UpdateCategoryRequest;
use App\Services\Admin\CategoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Get all categories with optional filters (admin only)
     * GET /admin/categories?per_page=20&include_deleted=false
     */
    public function index(FilterCategoryRequest $request)
    {
        try {
            $categories = $this->categoryService->getCategories(
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'data' => $categories,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch categories: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get specific category (admin only)
     * GET /admin/categories/{id}
     */
    public function show($id)
    {
        try {
            $category = $this->categoryService->getCategory($id);

            return response()->json([
                'success' => true,
                'data' => $category,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch category: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 404);
        }
    }

    /**
     * Create category (admin only)
     * POST /admin/categories
     */
    public function store(StoreCategoryRequest $request)
    {
        try {
            $category = DB::transaction(function () use ($request) {
                return $this->categoryService->createCategory(
                    $request->validated()
                );
            });

            Log::info('Category created', [
                'category_id' => $category->id,
                'admin_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully.',
                'data' => $category,
            ], 201);
        } catch (Exception $e) {
            Log::error('Failed to create category: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create category.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update category (admin only)
     * PATCH /admin/categories/{id}
     */
    public function update(UpdateCategoryRequest $request, $id)
    {
        try {
            $category = DB::transaction(function () use ($request, $id) {
                return $this->categoryService->updateCategory(
                    $id,
                    $request->validated()
                );
            });

            Log::info('Category updated', [
                'category_id' => $id,
                'admin_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.',
                'data' => $category,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to update category: ' . $e->getMessage());

            $statusCode = str_contains($e->getMessage(), 'not found') ? 404 : 500;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], $statusCode);
        }
    }

    /**
     * Delete category (soft delete) (admin only)
     * DELETE /admin/categories/{id}
     */
    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $this->categoryService->deleteCategory($id);
            });

            Log::info('Category deleted', [
                'category_id' => $id,
                'admin_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully.',
            ]);
        } catch (Exception $e) {
            Log::error('Failed to delete category: ' . $e->getMessage());

            $statusCode = str_contains($e->getMessage(), 'not found') ? 404 : 409;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], $statusCode);
        }
    }

    /**
     * Force delete category (permanent) (admin only)
     * DELETE /admin/categories/{id}/force
     */
    public function forceDelete($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $this->categoryService->forceDeleteCategory($id);
            });

            Log::warning('Category force deleted', [
                'category_id' => $id,
                'admin_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category permanently deleted.',
            ]);
        } catch (Exception $e) {
            Log::error('Failed to force delete category: ' . $e->getMessage());

            $statusCode = str_contains($e->getMessage(), 'not found') ? 404 : 500;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], $statusCode);
        }
    }

    /**
     * Restore category (admin only)
     * POST /admin/categories/{id}/restore
     */
    public function restore($id)
    {
        try {
            $category = DB::transaction(function () use ($id) {
                return $this->categoryService->restoreCategory($id);
            });

            Log::info('Category restored', [
                'category_id' => $id,
                'admin_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category restored successfully.',
                'data' => $category,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to restore category: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 404);
        }
    }

    /**
     * Get deleted categories (admin only)
     * GET /admin/categories/deleted
     */
    public function getDeleted()
    {
        try {
            $categories = $this->categoryService->getDeletedCategories();

            return response()->json([
                'success' => true,
                'data' => $categories,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch deleted categories: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch deleted categories.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get category statistics (admin only)
     * GET /admin/categories/stats
     */
    public function stats()
    {
        try {
            $stats = $this->categoryService->getStats();

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch category stats: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
