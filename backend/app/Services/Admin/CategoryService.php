<?php

namespace App\Services\Admin;

use App\Models\Category;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryService
{
    /**
     * Get all categories with optional filters
     */
    public function getCategories(array $filters): LengthAwarePaginator
    {
        $query = Category::query();

        // Include soft deleted if requested
        if (isset($filters['include_deleted']) && $filters['include_deleted']) {
            $query->withTrashed();
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->orderBy('name')->paginate($perPage);
    }

    /**
     * Get a specific category
     */
    public function getCategory(int $categoryId): Category
    {
        $category = Category::find($categoryId);

        if (!$category) {
            throw new Exception('Category not found.');
        }

        return $category;
    }

    /**
     * Create a category
     */
    public function createCategory(array $data): Category
    {
        return Category::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
    }

    /**
     * Update a category
     */
    public function updateCategory(int $categoryId, array $data): Category
    {
        $category = Category::find($categoryId);

        if (!$category) {
            throw new Exception('Category not found.');
        }

        $category->update($data);

        return $category->fresh();
    }

    /**
     * Delete a category (soft delete)
     */
    public function deleteCategory(int $categoryId): void
    {
        $category = Category::find($categoryId);

        if (!$category) {
            throw new Exception('Category not found.');
        }

        // Check if category has listings
        if ($category->listings()->count() > 0) {
            throw new Exception('Cannot delete category with existing listings.');
        }

        $category->delete();
    }

    /**
     * Restore a soft-deleted category
     */
    public function restoreCategory(int $categoryId): Category
    {
        $category = Category::onlyTrashed()->find($categoryId);

        if (!$category) {
            throw new Exception('Category not found or is not deleted.');
        }

        $category->restore();

        return $category;
    }

    /**
     * Force delete a category (permanent)
     */
    public function forceDeleteCategory(int $categoryId): void
    {
        $category = Category::withTrashed()->find($categoryId);

        if (!$category) {
            throw new Exception('Category not found.');
        }

        $category->forceDelete();
    }

    /**
     * Get deleted categories
     */
    public function getDeletedCategories(int $perPage = 15): LengthAwarePaginator
    {
        return Category::onlyTrashed()
            ->orderByDesc('deleted_at')
            ->paginate($perPage);
    }

    /**
     * Get category statistics
     */
    public function getStats(): array
    {
        return [
            'total' => Category::count(),
            'active' => Category::active()->count(),
            'deleted' => Category::onlyTrashed()->count(),
            'with_listings' => Category::withCount('listings')
                ->having('listings_count', '>', 0)
                ->count(),
        ];
    }
}
