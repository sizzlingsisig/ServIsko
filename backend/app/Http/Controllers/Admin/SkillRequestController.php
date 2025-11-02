<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\SkillRequestService;
use App\Http\Requests\Admin\ApproveSkillRequestRequest;
use App\Http\Requests\Admin\RejectSkillRequestRequest;
use App\Http\Requests\Admin\GetSkillRequestsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class SkillRequestController extends Controller
{
    public function __construct(private SkillRequestService $skillRequestService) {}

    /**
     * Get all skill requests (with filters)
     */
    public function index(GetSkillRequestsRequest $request)
    {
        try {
            $requests = $this->skillRequestService->getAllRequests($request->validated());

            return response()->json([
                'success' => true,
                'data' => $requests->items(),
                'pagination' => [
                    'total' => $requests->total(),
                    'per_page' => $requests->perPage(),
                    'current_page' => $requests->currentPage(),
                    'last_page' => $requests->lastPage(),
                ],
            ], 200);

        } catch (Exception $e) {
            Log::error('Get skill requests error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch skill requests.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get specific skill request details
     */
    public function show($id)
    {
        try {
            $skillRequest = $this->skillRequestService->getRequestById($id);

            return response()->json([
                'success' => true,
                'data' => $skillRequest,
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Skill request not found.',
            ], 404);

        } catch (Exception $e) {
            Log::error('Get skill request error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch skill request.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Approve a skill request
     */
    public function approve(ApproveSkillRequestRequest $request, $id)
    {
        try {
            $skillRequest = $this->skillRequestService->getRequestById($id);

            $approved = $this->skillRequestService->approveRequest(
                $skillRequest,
                $request->validated()
            );

            Log::info('Skill request approved', [
                'request_id' => $id,
                'admin_id' => auth()->user()->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Skill request approved successfully!',
                'data' => $approved,
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Skill request not found.',
            ], 404);

        } catch (Exception $e) {
            Log::error('Approve skill request error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 422);
        }
    }

    /**
     * Reject a skill request
     */
    public function reject(RejectSkillRequestRequest $request, $id)
    {
        try {
            $skillRequest = $this->skillRequestService->getRequestById($id);

            $rejected = $this->skillRequestService->rejectRequest(
                $skillRequest,
                $request->validated()['reason'] ?? null
            );

            Log::info('Skill request rejected', [
                'request_id' => $id,
                'admin_id' => auth()->user()->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Skill request rejected successfully!',
                'data' => $rejected,
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Skill request not found.',
            ], 404);

        } catch (Exception $e) {
            Log::error('Reject skill request error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 422);
        }
    }

    /**
     * Get skill requests statistics
     */
    public function stats(Request $request)
    {
        try {
            $validated = $request->validate([
                'date_range' => 'nullable|string|in:all,day,week,month',
            ]);

            $dateRange = $validated['date_range'] ?? 'all';
            $stats = $this->skillRequestService->getStats($dateRange);

            return response()->json([
                'success' => true,
                'data' => $stats,
            ], 200);

        } catch (Exception $e) {
            Log::error('Get skill request stats error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
