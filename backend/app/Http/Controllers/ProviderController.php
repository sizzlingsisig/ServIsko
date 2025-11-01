<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProviderProfileRequest;
use App\Http\Requests\AddProviderSkillRequest;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class ProviderController extends Controller
{
    /**
     * Get authenticated provider profile with skills
     */
    public function show(Request $request)
    {
        try {
            $user = $request->user()->load('providerProfile.skills');

            return response()->json([
                'success' => true,
                'data' => $user,
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

    /**
     * Update provider profile (title and links)
     */
    public function updateProfile(UpdateProviderProfileRequest $request)
    {
        try {
            $validated = $request->validated();
            $user = $request->user();

            // Ensure profile exists
            if (!$user->providerProfile) {
                $user->providerProfile()->create([
                    'title' => null,
                    'links' => [],
                ]);
            }

            $user->providerProfile()->update([
                'title' => $validated['title'] ?? null,
                'links' => $validated['links'] ?? [],
            ]);

            Log::info('Provider profile updated', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully!',
                'data' => $user->load('providerProfile.skills'),
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


        /**
     * Get all available skills
     */
    public function getSkills(Request $request)
    {
        try {
            $skills = Skill::ordered()->get();

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

    /**
     * Add skill to provider profile
     */
    public function addSkill(AddProviderSkillRequest $request)
    {
        try {
            $validated = $request->validated();
            $user = $request->user();

            // Ensure profile exists
            if (!$user->providerProfile) {
                $user->providerProfile()->create([
                    'title' => null,
                    'links' => [],
                ]);
            }

            // Check if skill already attached
            if ($user->providerProfile->skills()->where('skill_id', $validated['skill_id'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This skill is already added.',
                ], 422);
            }

            // Attach skill
            $user->providerProfile()->skills()->attach($validated['skill_id']);

            Log::info('Skill added to provider profile', [
                'user_id' => $user->id,
                'skill_id' => $validated['skill_id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Skill added successfully!',
                'data' => $user->load('providerProfile.skills'),
            ], 201);

        } catch (Exception $e) {
            Log::error('Add skill error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to add skill.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Remove skill from provider profile
     */
    public function removeSkill(Request $request, $skillId)
    {
        try {
            $user = $request->user();

            if (!$user->providerProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Provider profile not found.',
                ], 404);
            }

            // Check if skill exists
            if (!$user->providerProfile->skills()->where('skill_id', $skillId)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Skill not found in profile.',
                ], 404);
            }

            // Detach skill
            $user->providerProfile()->skills()->detach($skillId);

            Log::info('Skill removed from provider profile', [
                'user_id' => $user->id,
                'skill_id' => $skillId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Skill removed successfully!',
                'data' => $user->load('providerProfile.skills'),
            ], 200);

        } catch (Exception $e) {
            Log::error('Remove skill error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove skill.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Search skills
     */
    public function searchSkills(Request $request)
    {
        try {
            $search = $request->query('q', '');

            if (empty($search)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search query is required.',
                ], 422);
            }

            $skills = Skill::search($search)->ordered()->get();

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
            ], 500);
        }
    }
}
