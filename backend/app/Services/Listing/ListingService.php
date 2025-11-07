<?php

namespace App\Services\Listing;

use App\Models\Listing;
use App\Models\Tag;
use Exception;

class ListingService
{
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

        if (!empty($data['tags'])) {
            $this->syncTags($listing, $data['tags']);
        }

        return $listing;
    }

    /**
     * Update listing with tags
     */
    public function updateListing(Listing $listing, array $data): Listing
    {
        $listing->update($data);

        if (isset($data['tags'])) {
            $this->syncTags($listing, $data['tags']);
        }

        return $listing;
    }

    /**
     * Add a single tag to listing
     * Throws exception if tag not found or already added
     */
    public function addTag(Listing $listing, int $tagId): Listing
    {
        $tag = Tag::where('id', $tagId)->first();

        if (!$tag) {
            throw new Exception('Tag not found.');
        }

        if ($listing->tags()->where('tag_id', $tagId)->exists()) {
            throw new Exception('This tag is already added to this listing.');
        }

        $listing->tags()->syncWithoutDetaching([$tagId]);

        return $listing->fresh()->load('tags');
    }

    /**
     * Remove a tag from listing
     * Throws exception if tag not found
     */
    public function removeTag(Listing $listing, int $tagId): Listing
    {
        $tag = Tag::where('id', $tagId)->first();

        if (!$tag) {
            throw new Exception('Tag not found.');
        }

        $listing->tags()->detach($tagId);

        return $listing->fresh()->load('tags');
    }

    /**
     * Sync tags - normalize and attach to listing
     */
    public function syncTags(Listing $listing, array $tagNames): void
    {
        $tagIds = collect($tagNames)->map(function ($tagName) {
            $normalized = strtolower(trim($tagName));

            return Tag::firstOrCreate(
                ['name' => $normalized],
                ['name' => $normalized]
            )->id;
        });

        $listing->tags()->sync($tagIds);
    }

    /**
     * Delete a listing
     */
    public function deleteListing(Listing $listing): void
    {
        $listing->tags()->detach();
        $listing->delete();
    }
}
