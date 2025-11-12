<?php

namespace App\Http\Controllers\Seeker;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seeker\FilterApplicationRequest;
use App\Services\Seeker\ApplicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ApplicationController extends Controller
{
    protected $applicationService;

    public function __construct(ApplicationService $applicationService)
    {
        $this->applicationService = $applicationService;
    }

    /**
     * Get all applications for a listing (seeker only)
     * GET /seeker/listings/{listingId}/applications?status=pending&per_page=20
     */
    public function index(FilterApplicationRequest $request, $listingId)
    {
        try {
            $validated = $request->validated();

            $applications = $this->applicationService->getApplicationsForListing(
                auth()->id(),
                $listingId,
                $validated['status'] ?? null,
                $validated['per_page'] ?? 15
            );

            return response()->json([
                'success' => true,
                'data' => $applications,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch applications: ' . $e->getMessage());

            $statusCode = str_contains($e->getMessage(), 'not found') ? 404 : 500;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], $statusCode);
        }
    }

    /**
     * Show a specific application for a listing (seeker only)
     * GET /seeker/listings/{listingId}/applications/{applicationId}
     */
    public function show($listingId, $applicationId)
    {
        try {
            $application = $this->applicationService->getApplicationForListing(
                auth()->id(),
                $listingId,
                $applicationId
            );

            return response()->json([
                'success' => true,
                'data' => $application,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch application: ' . $e->getMessage());

            $statusCode = str_contains($e->getMessage(), 'not found') ? 404 : 500;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], $statusCode);
        }
    }

    /**
     * Accept an application
     * POST /seeker/listings/{listingId}/applications/{applicationId}/accept
     */
    public function accept($listingId, $applicationId)
    {
        try {
            $application = DB::transaction(function () use ($listingId, $applicationId) {
                return $this->applicationService->acceptApplication(
                    auth()->id(),
                    $listingId,
                    $applicationId
                );
            });

            Log::info('Application accepted', [
                'application_id' => $applicationId,
                'listing_id' => $listingId,
                'seeker_id' => auth()->id(),
                'hired_user_id' => $application->user_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application accepted successfully!',
                'data' => $application,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to accept application: ' . $e->getMessage());

            $statusCode = str_contains($e->getMessage(), 'not found') ? 404 : 409;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], $statusCode);
        }
    }

    /**
     * Reject an application
     * POST /seeker/listings/{listingId}/applications/{applicationId}/reject
     */
    public function reject($listingId, $applicationId)
    {
        try {
            $application = DB::transaction(function () use ($listingId, $applicationId) {
                return $this->applicationService->rejectApplication(
                    auth()->id(),
                    $listingId,
                    $applicationId
                );
            });

            Log::info('Application rejected', [
                'application_id' => $applicationId,
                'listing_id' => $listingId,
                'seeker_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application rejected.',
                'data' => $application,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to reject application: ' . $e->getMessage());

            $statusCode = str_contains($e->getMessage(), 'not found') ? 404 : 409;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], $statusCode);
        }
    }
}
