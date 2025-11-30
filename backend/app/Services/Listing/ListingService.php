<?php

namespace App\Services\Listing;

use App\Models\Listing;
use App\Models\Tag;
use Exception;

class ListingService
{
    /**
     * Get all active, non-expired listings (public browsing)
     */
    public function getAllListings(array $filters = [])
    {
        $query = Listing::with(['seeker', 'category', 'tags'])
            ->where('status', 'active')
            ->where(function ($q) {
                // Only show non-expired listings publicly
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });

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

        // Filtering expired/active listings (for future extensions)
        if (isset($filters['expired'])) {
            if ($filters['expired']) {
                $query->whereNotNull('expires_at')->where('expires_at', '<=', now());
            } else {
                $query->where(function($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
                });
            }
            unset($filters['expired']);
        }

        return $this->applyFilters($query, $filters);
    }

    /**
     * Apply filters to listing query
     */
    private function applyFilters($query, array $filters)
    {
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('title', 'ilike', "%{$filters['search']}%")
                  ->orWhere('description', 'ilike', "%{$filters['search']}%");
            });
        }

        if (isset($filters['min_budget'])) {
            $query->where('budget', '>=', $filters['min_budget']);
        }

        if (isset($filters['max_budget'])) {
            $query->where('budget', '<=', $filters['max_budget']);
        }

        if (isset($filters['tags']) && is_array($filters['tags'])) {
            $query->whereHas('tags', function($q) use ($filters) {
                $q->whereIn('tags.id', $filters['tags']);
            });
        }

        if (isset($filters['has_hired_user'])) {
            $filters['has_hired_user']
                ? $query->whereNotNull('hired_user_id')
                : $query->whereNull('hired_user_id');
        }

        // Filter by specific tag id
        if (isset($filters['tag_id'])) {
            $query->whereHas('tags', function($q) use ($filters) {
                $q->where('tags.id', $filters['tag_id']);
            });
        }

        // Filter by created date range
        if (isset($filters['created_from'])) {
            $query->where('created_at', '>=', $filters['created_from']);
        }

        if (isset($filters['created_to'])) {
            $query->where('created_at', '<=', $filters['created_to']);
        }

        // Filter by expires_at date range (optional/future use)
        if (isset($filters['expires_from'])) {
            $query->where('expires_at', '>=', $filters['expires_from']);
        }
        if (isset($filters['expires_to'])) {
            $query->where('expires_at', '<=', $filters['expires_to']);
        }

        // Smart flexible sorting (only one orderBy, prefer frontend smart sorts)
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
                // case 'rating':
                //     $query->orderBy('rating', 'desc');
                //     break;
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        } else {
            // Default sort
            $query->orderBy('created_at', 'desc');
        }

        $perPage = $filters['per_page'] ?? 15;
        return $query->paginate($perPage);
    }

}
