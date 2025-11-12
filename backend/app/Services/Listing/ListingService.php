<?php

namespace App\Services\Listing;

use App\Models\Listing;
use App\Models\Tag;
use Exception;

class ListingService
{
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

        if (isset($filters['has_hired_user'])) {
            $filters['has_hired_user']
                ? $query->whereNotNull('hired_user_id')
                : $query->whereNull('hired_user_id');
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
        ]);

        if (!empty($data['tag_ids'])) {
            $listing->tags()->sync($data['tag_ids']);
        }

        return $listing->fresh()->load(['tags', 'category']);
    }

    /**
     * Update listing with tags
     */
    public function updateListing(Listing $listing, array $data): Listing
    {
        $listing->update($data);

        if (isset($data['tag_ids'])) {
            $listing->tags()->sync($data['tag_ids']);
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
     * Remove a tag from listing
     * Throws exception if tag not found
     */
    public function removeTag(Listing $listing, int $tagId): Listing
    {
        $tag = Tag::find($tagId);

        if (!$tag) {
            throw new Exception('Tag not found.');
        }

        $listing->tags()->detach($tagId);

        return $listing->fresh()->load('tags');
    }

    /**
     * Delete a listing (soft delete)
     */
    public function deleteListing(Listing $listing): void
    {
        $listing->delete();
    }
}
