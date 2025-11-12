<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Provider\Application\StoreApplicationRequest;
use App\Http\Requests\Provider\Application\UpdateApplicationRequest;
use App\Services\Provider\ApplicationService;
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
     * Create a new application to a listing
     * POST /provider/listings/{listingId}/applications
     */
    public function store(StoreApplicationRequest $request, $listingId)
    {
        try {
            $application = DB::transaction(function () use ($request, $listingId) {
                return $this->applicationService->createApplication(
                    auth()->id(),
                    $listingId,
                    $request->input('message')
                );
            });

            Log::info('Application created', [
                'application_id' => $application->id,
                'user_id' => auth()->id(),
                'listing_id' => $listingId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application submitted successfully!',
                'data' => $application->load('listing', 'listing.seeker'),
            ], 201);
        } catch (Exception $e) {
            Log::error('Failed to create application: ' . $e->getMessage());

            $statusCode = str_contains($e->getMessage(), 'already applied') ? 409 : 400;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], $statusCode);
        }
    }

    /**
     * Get all applications for the authenticated provider
     * GET /provider/applications
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);

            $applications = $this->applicationService->getMyApplications(
                auth()->id(),
                $perPage
            );

            return response()->json([
                'success' => true,
                'data' => $applications,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch applications: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch applications.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Show a specific application of the provider
     * GET /provider/applications/{applicationId}
     */
    public function show($applicationId)
    {
        try {
            $application = $this->applicationService->getMyApplication(
                auth()->id(),
                $applicationId
            );

            return response()->json([
                'success' => true,
                'data' => $application,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch application: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 404);
        }
    }

    /**
     * Update provider's application
     * PATCH /provider/applications/{applicationId}
     */
    public function update(UpdateApplicationRequest $request, $applicationId)
    {
        try {
            $application = DB::transaction(function () use ($request, $applicationId) {
                return $this->applicationService->updateMyApplication(
                    auth()->id(),
                    $applicationId,
                    $request->input('message')
                );
            });

            Log::info('Application updated', [
                'application_id' => $applicationId,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application updated successfully.',
                'data' => $application,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to update application: ' . $e->getMessage());

            $statusCode = str_contains($e->getMessage(), 'not found') ? 404 : 409;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], $statusCode);
        }
    }

    /**
     * Withdraw provider's application
     * DELETE /provider/applications/{applicationId}
     */
    public function destroy($applicationId)
    {
        try {
            DB::transaction(function () use ($applicationId) {
                $this->applicationService->withdrawApplication(
                    auth()->id(),
                    $applicationId
                );
            });

            Log::info('Application withdrawn', [
                'application_id' => $applicationId,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application withdrawn successfully.',
            ]);
        } catch (Exception $e) {
            Log::error('Failed to withdraw application: ' . $e->getMessage());

            $statusCode = str_contains($e->getMessage(), 'not found') ? 404 : 409;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], $statusCode);
        }
    }
}
