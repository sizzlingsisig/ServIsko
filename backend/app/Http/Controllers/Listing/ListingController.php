<?php

namespace App\Http\Controllers\Listing;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class ListingController extends Controller
{
    /**
     * Public listing browse
     * GET /listings
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Validate request
            $validated = $request->validate([
                'category'     => ['nullable', 'integer', 'exists:categories,id'],
                'search'       => ['nullable', 'string', 'max:255'],
                'minBudget'    => ['nullable', 'numeric', 'min:0'],
                'maxBudget'    => ['nullable', 'numeric', 'min:0'],
                'sort_by'      => ['nullable', 'in:newest,oldest,price_low,price_high,rating'],
                'page'         => ['nullable', 'integer', 'min:1'],
                'per_page'     => ['nullable', 'integer', 'min:1', 'max:100'],
                'expires_from' => ['nullable', 'date'],
                'expires_to'   => ['nullable', 'date'],
            ], [
                'category.exists'   => 'Selected category does not exist.',
                'search.max'        => 'Search term cannot exceed 255 characters.',
                'minBudget.numeric' => 'Minimum budget must be a valid number.',
                'minBudget.min'     => 'Minimum budget cannot be negative.',
                'maxBudget. numeric' => 'Maximum budget must be a valid number.',
                'maxBudget.min'     => 'Maximum budget cannot be negative.',
                'sort_by. in'        => 'Sort by must be one of: newest, oldest, price_low, price_high, rating.',
                'page. integer'      => 'Page must be a valid integer.',
                'page.min'          => 'Page must be at least 1.',
                'per_page.integer'  => 'Per page must be a valid integer.',
                'per_page.min'      => 'Per page must be at least 1.',
                'per_page.max'      => 'Per page cannot exceed 100.',
                'expires_from.date' => 'Expires From must be a valid date.',
                'expires_to.date'   => 'Expires To must be a valid date.',
            ]);

            $listings = $this->getAllListings($validated);

            return response()->json([
                'success' => true,
                'data' => $listings,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Failed to fetch all listings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch listings.',
            ], 500);
        }
    }

    /**
     * Show listing details (public)
     * GET /listings/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            if ($id <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid listing ID.',
                ], 400);
            }

            $listing = Listing::with(['seeker', 'category', 'tags', 'hiredUser', 'applications'])
                ->findOrFail($id);

            // Add expiry status to response
            $data = $listing->toArray();
            $data['is_expired'] = $listing->isExpired();

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Listing not found.',
            ], 404);
        } catch (Exception $e) {
            Log::error('Failed to fetch listing', [
                'error' => $e->getMessage(),
                'listing_id' => $id,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch listing.',
            ], 500);
        }
    }

    // ========================================================================
    // PRIVATE HELPER METHODS
    // ========================================================================

    /**
     * Get all active, non-expired listings (public browsing)
     */
    private function getAllListings(array $filters = [])
    {
        $query = Listing::with(['seeker', 'category', 'tags'])
            ->where('status', 'active')
            ->where(function ($q) {
                // Only show non-expired listings publicly
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->whereNot('user_id', auth()->id());

        // Map frontend to backend keys
        if (isset($filters['category'])) {
            $filters['category_id'] = $filters['category'];
            unset($filters['category']);
        }
        if (isset($filters['minBudget'])) {
            $filters['min_budget'] = $filters['minBudget'];
            unset($filters['minBudget']);
        }
        if (isset($filters['maxBudget'])) {
            $filters['max_budget'] = $filters['maxBudget'];
            unset($filters['maxBudget']);
        }
        if (isset($filters['minRating'])) {
            $filters['min_rating'] = $filters['minRating'];
            unset($filters['minRating']);
        }

        return $this->applyFilters($query, $filters);
    }

    /**
     * Apply filters to listing query
     */
    private function applyFilters($query, array $filters)
    {
        // Status filter
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Category filter
        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Search filter (case-insensitive using PostgreSQL ilike)
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        // Budget filters
        if (isset($filters['min_budget'])) {
            $query->where('budget', '>=', $filters['min_budget']);
        }

        if (isset($filters['max_budget'])) {
            $query->where('budget', '<=', $filters['max_budget']);
        }

        // Tags filter (multiple tags)
        if (isset($filters['tags']) && is_array($filters['tags'])) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->whereIn('tags.id', $filters['tags']);
            });
        }

        // Single tag filter
        if (isset($filters['tag_id'])) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->where('tags.id', $filters['tag_id']);
            });
        }

        // Hired user filter
        if (isset($filters['has_hired_user'])) {
            $filters['has_hired_user']
                ? $query->whereNotNull('hired_user_id')
                : $query->whereNull('hired_user_id');
        }

        // Created date range filters
        if (isset($filters['created_from'])) {
            $query->where('created_at', '>=', $filters['created_from']);
        }

        if (isset($filters['created_to'])) {
            $query->where('created_at', '<=', $filters['created_to']);
        }

        // Expires date range filters
        if (isset($filters['expires_from'])) {
            $query->where('expires_at', '>=', $filters['expires_from']);
        }

        if (isset($filters['expires_to'])) {
            $query->where('expires_at', '<=', $filters['expires_to']);
        }

        // Sorting
        if (isset($filters['sort_by'])) {
            switch ($filters['sort_by']) {
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'price_low':
                    $query->orderBy('budget', 'asc');
                    break;
                case 'price_high':
                    $query->orderBy('budget', 'desc');
                    break;
                case 'rating':
                    // TODO: Implement rating sorting when rating system is added
                    $query->orderBy('created_at', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        } else {
            // Default sort: newest first
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $perPage = min($filters['per_page'] ?? 15, 100);

        return $query->paginate($perPage);
    }
}
