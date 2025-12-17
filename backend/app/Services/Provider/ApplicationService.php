<?php

namespace App\Services\Provider;

use App\Models\Application;
use App\Models\Listing;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;

class ApplicationService
{
    /**
     * Create application for a listing
     */
    public function createApplication(int $userId, int $listingId, ?string $message): Application
    {
        // Check if listing exists
        $listing = Listing::where('id', $listingId)->first();
        if (!$listing) {
            throw new Exception('Listing not found.');
        }

        // Prevent self-application
        if ($listing->seeker_user_id === $userId) {
            throw new Exception('You cannot apply to your own listing.');
        }

        // Check if already applied
        $existing = Application::where('listing_id', $listingId)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            throw new Exception('You already applied to this listing.');
        }

        return Application::create([
            'user_id' => $userId,
            'listing_id' => $listingId,
            'status' => 'pending',
            'message' => $message,
        ]);
    }

    /**
     * Get all applications for the provider
     */
    public function getMyApplications(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Application::where('user_id', $userId)
            ->with(['listing', 'listing.seeker', 'listing.category'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get specific application for the provider
     */
    public function getMyApplication(int $userId, int $applicationId): Application
    {
        $application = Application::where('user_id', $userId)
            ->where('id', $applicationId)
            ->first();

        if (!$application) {
            throw new Exception('Application not found.');
        }

        $application->load(['listing', 'listing.seeker', 'listing.category', 'listing.tags']);

        return $application;
    }

    /**
     * Update provider's application
     */
    public function updateMyApplication(int $userId, int $applicationId, ?string $message): Application
    {
        $application = Application::where('user_id', $userId)
            ->where('id', $applicationId)
            ->first();

        if (!$application) {
            throw new Exception('Application not found.');
        }

        if ($application->status !== 'pending') {
            throw new Exception('Cannot update an application that is not pending.');
        }

        $application->update([
            'message' => $message,
        ]);

        return $application->fresh()->load('listing', 'listing.seeker', 'listing.category');
    }

    /**
     * Withdraw provider's application
     */
    public function withdrawApplication(int $userId, int $applicationId): void
    {
        $application = Application::where('user_id', $userId)
            ->where('id', $applicationId)
            ->first();

        if (!$application) {
            throw new Exception('Application not found.');
        }

        if ($application->status !== 'pending') {
            throw new Exception('Cannot withdraw an application that is not pending.');
        }

        $application->delete();
    }
}
