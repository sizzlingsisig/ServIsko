<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterApplicationRequest;
use App\Services\Admin\ApplicationService;
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
     * Get all applications with optional filters (admin only)
     * GET /admin/applications?status=pending&listing_id=1&user_id=5&per_page=20
     */
    public function index(FilterApplicationRequest $request)
    {
        try {
            $applications = $this->applicationService->getApplications(
                $request->validated()
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
     * Show a specific application (admin only)
     * GET /admin/applications/{applicationId}
     */
    public function show($applicationId)
    {
        try {
            $application = $this->applicationService->getApplication($applicationId);

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
     * Get application statistics (admin only)
     * GET /admin/applications/stats
     */
    public function stats()
    {
        try {
            $stats = $this->applicationService->getStats();

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch application stats: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Delete an application (admin only)
     * DELETE /admin/applications/{applicationId}
     */
    public function destroy($applicationId)
    {
        try {
            $this->applicationService->deleteApplication($applicationId);

            Log::info('Application deleted by admin', [
                'application_id' => $applicationId,
                'admin_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application deleted successfully.',
            ]);
        } catch (Exception $e) {
            Log::error('Failed to delete application: ' . $e->getMessage());

            $statusCode = str_contains($e->getMessage(), 'not found') ? 404 : 500;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], $statusCode);
        }
    }
}
