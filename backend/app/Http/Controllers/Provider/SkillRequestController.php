<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Services\Provider\SkillRequestService;
use App\Http\Requests\Provider\RequestSkillRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class SkillRequestController extends Controller
{
    public function __construct(
        private SkillRequestService $skillRequestService
    ) {}

    /**
     * Submit a skill request
     */
    public function store(RequestSkillRequest $request)
    {
        try {
            $user = auth()->user();
            $skillRequest = $this->skillRequestService->createRequest(
                $user,
                $request->validated()
            );

            Log::info('Skill request submitted', [
                'user_id' => $user->id,
                'skill_name' => $request->name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Skill request submitted successfully!',
                'data' => $skillRequest,
            ], 201);

        } catch (Exception $e) {
            Log::error('Submit skill request error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 422);
        }
    }

    /**
     * Get user's skill requests
     */
    public function index(Request $request)
    {
        try {
            $validated = $request->validate(rules: [
                'status' => 'nullable|string|in:pending,approved,rejected',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            $user = auth()->user();
            $requests = $this->skillRequestService->getUserRequests($user, $validated);

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
                'message' => 'Failed to fetch requests.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update skill request
     */
    public function update($requestId, RequestSkillRequest $request)
    {
        try {
            $user = auth()->user();
            $skillRequest = $this->skillRequestService->updateRequest(
                $user,
                $requestId,
                $request->validated()
            );

            Log::info('Skill request updated', [
                'user_id' => $user->id,
                'request_id' => $requestId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Skill request updated successfully!',
                'data' => $skillRequest,
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Skill request not found.',
            ], 404);

        } catch (Exception $e) {
            Log::error('Update skill request error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 422);
        }
    }

    /**
     * Delete skill request
     */
    public function destroy($requestId)
    {
        try {
            $user = auth()->user();
            $this->skillRequestService->deleteRequest($user, $requestId);

            Log::info('Skill request deleted', [
                'user_id' => $user->id,
                'request_id' => $requestId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Skill request deleted successfully!',
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Skill request not found.',
            ], 404);

        } catch (Exception $e) {
            Log::error('Delete skill request error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 422);
        }
    }

    /**
     * Get skill request stats
     */
    public function stats()
    {
        try {
            $user = auth()->user();
            $stats = $this->skillRequestService->getUserRequestStats($user);

            return response()->json([
                'success' => true,
                'data' => $stats,
            ], 200);

        } catch (Exception $e) {
            Log::error('Get skill request stats error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch stats.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
