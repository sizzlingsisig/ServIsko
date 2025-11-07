<?php

namespace App\Services\Seeker;

use App\Models\Application;
use App\Models\Listing;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;

class ApplicationService
{
    /**
     * Get all applications for a listing (seeker only)
     */
    public function getApplicationsForListing(
        int $seekerId,
        int $listingId,
        ?string $status = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $listing = Listing::where('seeker_user_id', $seekerId)
            ->where('id', $listingId)
            ->first();

        if (!$listing) {
            throw new Exception('Listing not found.');
        }

        $query = Application::where('listing_id', $listingId)
            ->with(['user']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * Get specific application for a listing (seeker only)
     */
    public function getApplicationForListing(
        int $seekerId,
        int $listingId,
        int $applicationId
    ): Application {
        $listing = Listing::where('seeker_user_id', $seekerId)
            ->where('id', $listingId)
            ->first();

        if (!$listing) {
            throw new Exception('Listing not found.');
        }

        $application = Application::where('id', $applicationId)
            ->where('listing_id', $listingId)
            ->first();

        if (!$application) {
            throw new Exception('Application not found.');
        }

        $application->load(['user', 'listing', 'listing.category', 'listing.tags']);

        return $application;
    }

    /**
     * Accept an application
     */
    public function acceptApplication(
        int $seekerId,
        int $listingId,
        int $applicationId
    ): Application {
        $listing = Listing::where('seeker_user_id', $seekerId)
            ->where('id', $listingId)
            ->first();

        if (!$listing) {
            throw new Exception('Listing not found.');
        }

        $application = Application::where('id', $applicationId)
            ->where('listing_id', $listingId)
            ->first();

        if (!$application) {
            throw new Exception('Application not found.');
        }

        if ($listing->hired_user_id) {
            throw new Exception('This listing already has a hired user.');
        }

        if ($application->status !== 'pending') {
            throw new Exception('Can only accept pending applications.');
        }

        // Accept this application
        $application->update(['status' => 'accepted']);

        // Set hired_user_id on listing
        $listing->update([
            'hired_user_id' => $application->user_id,
            'status' => 'closed',
        ]);

        // Reject all other pending applications
        Application::where('listing_id', $listing->id)
            ->where('id', '!=', $application->id)
            ->where('status', 'pending')
            ->update(['status' => 'rejected']);

        return $application->fresh()->load('user');
    }

    /**
     * Reject an application
     */
    public function rejectApplication(
        int $seekerId,
        int $listingId,
        int $applicationId
    ): Application {
        $listing = Listing::where('seeker_user_id', $seekerId)
            ->where('id', $listingId)
            ->first();

        if (!$listing) {
            throw new Exception('Listing not found.');
        }

        $application = Application::where('id', $applicationId)
            ->where('listing_id', $listingId)
            ->first();

        if (!$application) {
            throw new Exception('Application not found.');
        }

        if ($application->status !== 'pending') {
            throw new Exception('Can only reject pending applications.');
        }

        $application->update(['status' => 'rejected']);

        return $application;
    }
}
