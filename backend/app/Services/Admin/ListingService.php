<?php

namespace App\Services\Admin;

use App\Models\Listing;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;

class ListingService
{
    /**
     * Get all listings with optional filters
     */
    public function getListings(array $filters): LengthAwarePaginator
    {
        $query = Listing::with(['seeker', 'category', 'tags', 'hiredUser', 'applications']);

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by category
        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Filter by seeker
        if (isset($filters['seeker_id'])) {
            $query->where('seeker_user_id', $filters['seeker_id']);
        }

        // Filter by hired user status
        if (isset($filters['has_hired_user'])) {
            if ($filters['has_hired_user']) {
                $query->whereNotNull('hired_user_id');
            } else {
                $query->whereNull('hired_user_id');
            }
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * Get a specific listing
     */
    public function getListing(int $listingId): Listing
    {
        $listing = Listing::where('id', $listingId)->first();

        if (!$listing) {
            throw new Exception('Listing not found.');
        }

        $listing->load(['seeker', 'category', 'tags', 'hiredUser', 'applications']);

        return $listing;
    }

    /**
     * Get listing statistics
     */
    public function getStats(): array
    {
        return [
            'total' => Listing::count(),
            'active' => Listing::where('status', 'active')->count(),
            'closed' => Listing::where('status', 'closed')->count(),
            'expired' => Listing::where('status', 'expired')->count(),
            'with_hired_user' => Listing::whereNotNull('hired_user_id')->count(),
            'without_hired_user' => Listing::whereNull('hired_user_id')->count(),
            'total_budget' => Listing::whereNotNull('budget')->sum('budget'),
            'by_status' => Listing::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get()
                ->toArray(),
        ];
    }

    /**
     * Delete a listing
     */
    public function deleteListing(int $listingId): void
    {
        $listing = Listing::where('id', $listingId)->first();

        if (!$listing) {
            throw new Exception('Listing not found.');
        }

        // Detach tags before deleting
        $listing->tags()->detach();
        $listing->delete();
    }


    /**
     * Restore a soft-deleted listing
     */
    public function restoreListing(int $listingId): Listing
    {
        $listing = Listing::onlyTrashed()->where('id', $listingId)->first();

        if (!$listing) {
            throw new Exception('Listing not found or is not deleted.');
        }

        $listing->restore();

        return $listing;
    }

    /**
     * Get deleted listings
     */
    public function getDeletedListings(int $perPage = 15): LengthAwarePaginator
    {
        return Listing::onlyTrashed()
            ->with(['seeker', 'category'])
            ->orderByDesc('deleted_at')
            ->paginate($perPage);
    }
}
