<?php

namespace App\Services\Listing;

use App\Models\Listing;
use App\Models\Tag;
use Exception;

class ListingService
{
    /**
     * Get all active listings (public browsing)
     */
  public function getAllListings(array $filters = [])
{
    $query = Listing::with(['seeker', 'category', 'tags'])
        ->where('status', 'active');

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
    // Sorting key is passed through
    // Add other mappings as needed
     * Get filtered listings for a user
     */
    public function getUserListings(int $userId, array $filters = [])
    {
        $query = Listing::where('seeker_user_id', $userId);

        // Apply active scope to filter out expired listings by default
        if (!($filters['include_expired'] ?? false)) {
            $query->active();
        }

        $query->with(['seeker', 'category', 'tags', 'hiredUser']);

    return $this->applyFilters($query, $filters);
}

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
        if (isset($filters['has_hired_user'])) {
            $filters['has_hired_user']
                ? $query->whereNotNull('hired_user_id')
                : $query->whereNull('hired_user_id');
        }

        // Filter by tag
        if (isset($filters['tag_id'])) {
            $query->whereHas('tags', function($q) use ($filters) {
                $q->where('tags.id', $filters['tag_id']);
            });
        }

        // Filter by date range
        if (isset($filters['created_from'])) {
            $query->where('created_at', '>=', $filters['created_from']);
        }

        if (isset($filters['created_to'])) {
            $query->where('created_at', '<=', $filters['created_to']);
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

    // Flexible sorting
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
                $query->orderBy('rating', 'desc');
                break;
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
        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    /**
     * Create a new listing with tags
     */
    public function createListing(array $data, int $userId): Listing
    {
        $listing = Listing::create([
            'seeker_user_id' => $userId,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'budget' => $data['budget'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'status' => 'active',
            'expires_at' => $data['expires_at'] ?? null,
        ]);

        if (!empty($data['tag_ids'])) {
            $listing->tags()->sync($data['tag_ids']);
        }

        // Handle tag names (if using tag names instead of IDs)
        if (!empty($data['tags'])) {
            $this->syncTagsByName($listing, $data['tags']);
        }

        return $listing->fresh()->load(['tags', 'category']);
    }

    /**
     * Update listing with tags
     */
    public function updateListing(Listing $listing, array $data): Listing
    {
        // Build update array
        $updateData = [];

        if (isset($data['title'])) {
            $updateData['title'] = $data['title'];
        }

        if (isset($data['description'])) {
            $updateData['description'] = $data['description'];
        }

        if (isset($data['budget'])) {
            $updateData['budget'] = $data['budget'];
        }

        if (isset($data['category_id'])) {
            $updateData['category_id'] = $data['category_id'];
        }

        if (isset($data['status'])) {
            $updateData['status'] = $data['status'];
        }

        if (isset($data['expires_at'])) {
            $updateData['expires_at'] = $data['expires_at'];
        }

        $listing->update($updateData);

        if (isset($data['tag_ids'])) {
            $listing->tags()->sync($data['tag_ids']);
        }

        // Handle tag names (if using tag names instead of IDs)
        if (isset($data['tags'])) {
            $this->syncTagsByName($listing, $data['tags']);
        }

        return $listing->fresh()->load(['tags', 'category']);
    }

    /**
     * Add a single tag to listing
     * Throws exception if tag not found or already added
     */
    public function addTag(Listing $listing, int $tagId): Listing
    {
        $tag = Tag::find($tagId);

        if (!$tag) {
            throw new Exception('Tag not found.');
        }

        if ($listing->tags()->where('tag_id', $tagId)->exists()) {
            throw new Exception('This tag is already added to this listing.');
        }

        $listing->tags()->attach($tagId);

        return $listing->fresh()->load('tags');
    }

    /**
     * Get filtered listings for a user
     */
    public function getUserListings(int $userId, array $filters = [])
    {
        $query = Listing::where('seeker_user_id', $userId)
            ->with(['seeker', 'category', 'tags', 'hiredUser']);

        return $this->applyFilters($query, $filters);
    }




    /**
     * Sync tags by name (creates tags if they don't exist)
     */
    protected function syncTagsByName(Listing $listing, array $tagNames): void
    {
        $tagIds = [];

        foreach ($tagNames as $tagName) {
            $tag = Tag::firstOrCreate(['name' => trim($tagName)]);
            $tagIds[] = $tag->id;
        }

        $listing->tags()->sync($tagIds);
    }
}
