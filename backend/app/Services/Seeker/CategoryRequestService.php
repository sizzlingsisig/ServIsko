<?php

namespace App\Services\Seeker;

use App\Models\CategoryRequest;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryRequestService
{
    /**
     * Create a category request
     */
    public function createCategoryRequest(int $userId, array $data): CategoryRequest
    {
        return CategoryRequest::create([
            'user_id' => $userId,
            'status' => 'pending',
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
    }

    /**
     * Get user's category requests with optional filters
     */
    public function getUserCategoryRequests(int $userId, array $filters): LengthAwarePaginator
    {
        $query = CategoryRequest::where('user_id', $userId);

        // Filter by status if provided
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * Get specific category request for user
     */
    public function getUserCategoryRequest(int $userId, int $requestId): CategoryRequest
    {
        $categoryRequest = CategoryRequest::where('user_id', $userId)
            ->where('id', $requestId)
            ->first();

        if (!$categoryRequest) {
            throw new Exception('Category request not found.');
        }

        return $categoryRequest;
    }
}
