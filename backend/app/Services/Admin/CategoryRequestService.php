<?php

namespace App\Services\Admin;

use App\Models\Category;
use App\Models\CategoryRequest;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryRequestService
{
    /**
     * Get all category requests with optional filters
     */
    public function getCategoryRequests(array $filters): LengthAwarePaginator
    {
        $query = CategoryRequest::with('user');

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by user
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * Get a specific category request
     */
    public function getCategoryRequest(int $requestId): CategoryRequest
    {
        $categoryRequest = CategoryRequest::with('user')->find($requestId);

        if (!$categoryRequest) {
            throw new Exception('Category request not found.');
        }

        return $categoryRequest;
    }

    /**
     * Get category request statistics
     */
    public function getStats(): array
    {
        return [
            'total' => CategoryRequest::count(),
            'pending' => CategoryRequest::where('status', 'pending')->count(),
            'approved' => CategoryRequest::where('status', 'approved')->count(),
            'rejected' => CategoryRequest::where('status', 'rejected')->count(),
            'by_status' => CategoryRequest::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get()
                ->toArray(),
        ];
    }

    /**
     * Approve category request and create category
     */
    public function approveCategoryRequest(int $requestId): CategoryRequest
    {
        $categoryRequest = CategoryRequest::find($requestId);

        if (!$categoryRequest) {
            throw new Exception('Category request not found.');
        }

        if ($categoryRequest->status !== 'pending') {
            throw new Exception('Only pending requests can be approved.');
        }

        // Create the new category
        Category::create([
            'name' => $categoryRequest->name,
            'description' => $categoryRequest->description,
        ]);

        // Update request status
        $categoryRequest->update([
            'status' => 'approved',
            'admin_notes' => 'Category created successfully.',
        ]);

        return $categoryRequest->fresh();
    }

    /**
     * Reject category request
     */
    public function rejectCategoryRequest(int $requestId, string $adminNotes): CategoryRequest
    {
        $categoryRequest = CategoryRequest::find($requestId);

        if (!$categoryRequest) {
            throw new Exception('Category request not found.');
        }

        if ($categoryRequest->status !== 'pending') {
            throw new Exception('Only pending requests can be rejected.');
        }

        $categoryRequest->update([
            'status' => 'rejected',
            'admin_notes' => $adminNotes,
        ]);

        return $categoryRequest->fresh();
    }

    /**
     * Delete category request
     */
    public function deleteCategoryRequest(int $requestId): void
    {
        $categoryRequest = CategoryRequest::find($requestId);

        if (!$categoryRequest) {
            throw new Exception('Category request not found.');
        }

        $categoryRequest->delete();
    }
}
