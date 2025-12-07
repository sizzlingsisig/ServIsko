<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Exception;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    /**
     * Get all categories (not deleted)
     * GET /categories
     */
    public function index()
    {
        try {
            $search = request()->input('search');
            $categoriesQuery = Category::orderBy('name');
            if ($search) {
                $categoriesQuery->where('name', 'ILIKE', "%$search%");
            }
            $categories = $categoriesQuery->get();

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
     * Get specific category (not deleted)
     * GET /categories/{id}
     */
    public function show($id)
    {
        try {
            $category = Category::find($id);

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
