<?php

namespace App\Services\Admin;

use App\Models\Application;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;

class ApplicationService
{
    /**
     * Get all applications with optional filters
     */
    public function getApplications(array $filters): LengthAwarePaginator
    {
        $query = Application::with(['user', 'listing', 'listing.seeker']);

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by listing
        if (isset($filters['listing_id'])) {
            $query->where('listing_id', $filters['listing_id']);
        }

        // Filter by user/applicant
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * Get a specific application
     */
    public function getApplication(int $applicationId): Application
    {
        $application = Application::where('id', $applicationId)->first();

        if (!$application) {
            throw new Exception('Application not found.');
        }

        $application->load(['user', 'listing', 'listing.seeker']);

        return $application;
    }

    /**
     * Get application statistics
     */
    public function getStats(): array
    {
        return [
            'total' => Application::count(),
            'pending' => Application::where('status', 'pending')->count(),
            'accepted' => Application::where('status', 'accepted')->count(),
            'rejected' => Application::where('status', 'rejected')->count(),
            'by_status' => Application::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get()
                ->toArray(),
        ];
    }

    /**
     * Delete an application
     */
    public function deleteApplication(int $applicationId): void
    {
        $application = Application::where('id', $applicationId)->first();

        if (!$application) {
            throw new Exception('Application not found.');
        }

        $application->delete();
    }
}
