<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Exception;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    /**
     * Get all active categories (public)
     * GET /categories
     */
    public function index()
    {
        try {
            $categories = Category::active()
                ->orderBy('name')
                ->get();

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
     * Get specific category (public)
     * GET /categories/{id}
     */
    public function show($id)
    {
        try {
            $category = Category::active()->find($id);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $category,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch category: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch category.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
