<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ProviderProfileService;
use App\Services\SkillRequestService;
use App\Http\Requests\UpdateProviderProfileRequest;
use App\Http\Requests\AddProviderSkillRequest;
use App\Http\Requests\RemoveProviderSkillRequest;
use App\Http\Requests\UpdateProviderSkillRequest;
use App\Http\Requests\RequestSkillRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class ProviderController extends Controller
{
    public function __construct(
        private ProviderProfileService $profileService,
        private SkillRequestService $skillRequestService
    ) {}

    public function show()
    {
        try {
            $user = auth()->user();
            $data = $this->profileService->getProfileWithSkills($user);

            return response()->json([
                'success' => true,
                'data' => $data,
            ], 200);

        } catch (Exception $e) {
            Log::error('Get provider profile error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch provider profile.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function updateProfile(UpdateProviderProfileRequest $request)
    {
        try {
            $user = auth()->user();
            $this->profileService->updateProfile($user, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully!',
                'data' => $this->profileService->getProfileWithSkills($user),
            ], 200);

        } catch (Exception $e) {
            Log::error('Update provider profile error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function addLink(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'url' => 'required|url|max:2048',
            ]);

            $user = auth()->user();
            $this->profileService->addLink($user, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Link added successfully!',
                'data' => $this->profileService->getProfileWithSkills($user),
            ], 201);

        } catch (Exception $e) {
            Log::error('Add link error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to add link.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 422);
        }
    }

    public function updateLink(Request $request, $linkId)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'url' => 'required|url|max:2048',
            ]);

            $user = auth()->user();
            $this->profileService->updateLink($user, $linkId, $validated);

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

    public function removeLink(Request $request, $linkId)
    {
        try {
            $user = auth()->user();
            $this->profileService->removeLink($user, $linkId);

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

    // ← CHANGE: Remove GetSkillsRequest dependency
    public function getSkills(Request $request)
    {
        try {
            $validated = $request->validate([
                'search' => 'nullable|string|max:255',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            $skills = $this->profileService->getAllSkills($validated);

            return response()->json([
                'success' => true,
                'data' => $skills,
            ], 200);

        } catch (Exception $e) {
            Log::error('Get skills error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch skills.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    // ← CHANGE: Remove SearchSkillsRequest dependency
    public function searchSkills(Request $request)
    {
        try {
            $validated = $request->validate([
                'q' => 'required|string|max:255',
            ]);

            $skills = $this->profileService->searchSkills($validated['q']);

            return response()->json([
                'success' => true,
                'data' => $skills,
            ], 200);

        } catch (Exception $e) {
            Log::error('Search skills error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to search skills.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 422);
        }
    }

    public function addSkill(AddProviderSkillRequest $request)
    {
        try {
            $user = auth()->user();
            $this->profileService->addSkill($user, $request->validated()['skill_id']);

            return response()->json([
                'success' => true,
                'message' => 'Skill added successfully!',
                'data' => $this->profileService->getProfileWithSkills($user),
            ], 201);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Skill not found.',
            ], 404);

        } catch (Exception $e) {
            Log::error('Add skill error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 422);
        }
    }

    public function updateSkill(UpdateProviderSkillRequest $request, $skillId)
    {
        try {
            $user = auth()->user();
            $this->profileService->updateSkill($user, $skillId, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Skill updated successfully!',
                'data' => $this->profileService->getProfileWithSkills($user),
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Skill not found.',
            ], 404);

        } catch (Exception $e) {
            Log::error('Update skill error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 422);
        }
    }

    public function removeSkill(RemoveProviderSkillRequest $request, $skillId)
    {
        try {
            $user = auth()->user();
            $this->profileService->removeSkill($user, $skillId);

            return response()->json([
                'success' => true,
                'message' => 'Skill removed successfully!',
                'data' => $this->profileService->getProfileWithSkills($user),
            ], 200);

        } catch (Exception $e) {
            Log::error('Remove skill error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 422);
        }
    }

    public function submitSkillRequest(RequestSkillRequest $request)
    {
        try {
            $user = auth()->user();
            $skillRequest = $this->skillRequestService->createRequest(
                $user,
                $request->validated()
            );

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

    public function getMySkillRequests()
    {
        try {
            $user = auth()->user();
            $requests = $this->skillRequestService->getUserRequests($user);

            return response()->json([
                'success' => true,
                'data' => $requests,
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
}
