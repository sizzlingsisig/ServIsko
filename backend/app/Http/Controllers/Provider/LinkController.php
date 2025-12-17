<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Services\Provider\LinkService;
use App\Services\Provider\ProfileService;
use App\Http\Requests\Provider\StoreLinkRequest;
use App\Http\Requests\Provider\UpdateLinkRequest;
use Illuminate\Support\Facades\Log;
use Exception;

class LinkController extends Controller
{
    public function __construct(
        private LinkService $linkService,
        private ProfileService $profileService
    ) {}

    /**
     * Get all links
     */
    public function index()
    {
        try {
            $user = auth()->user();
            $links = $this->linkService->getAll($user);

            return response()->json([
                'success' => true,
                'data' => $links,
            ], 200);

        } catch (Exception $e) {
            Log::error('Get links error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch links.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Add link
     */
    public function store(StoreLinkRequest $request)
    {
        try {
            $user = auth()->user();
            $this->linkService->addLink($user, $request->validated());

            Log::info('Link added', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Link added successfully!',
                'data' => $this->profileService->getProfileWithSkills($user),
            ], 201);

        } catch (Exception $e) {
            Log::error('Add link error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 422);
        }
    }

    /**
     * Update link
     */
    public function update($linkId, UpdateLinkRequest $request)
    {
        try {
            $user = auth()->user();
            $this->linkService->updateLink($user, $linkId, $request->validated());

            Log::info('Link updated', ['user_id' => $user->id, 'link_id' => $linkId]);

            return response()->json([
                'success' => true,
                'message' => 'Link updated successfully!',
                'data' => $this->profileService->getProfileWithSkills($user),
            ], 200);

        } catch (Exception $e) {
            Log::error('Update link error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 422);
        }
    }

    /**
     * Delete link
     */
    public function destroy($linkId)
    {
        try {
            $user = auth()->user();
            $this->linkService->removeLink($user, $linkId);

            Log::info('Link removed', ['user_id' => $user->id, 'link_id' => $linkId]);

            return response()->json([
                'success' => true,
                'message' => 'Link removed successfully!',
                'data' => $this->profileService->getProfileWithSkills($user),
            ], 200);

        } catch (Exception $e) {
            Log::error('Remove link error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 422);
        }
    }
}
