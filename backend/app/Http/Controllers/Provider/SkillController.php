<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Services\Provider\SkillService;
use App\Services\Provider\ProfileService;
use App\Http\Requests\Provider\AddProviderSkillRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class SkillController extends Controller
{
    public function __construct(
        private SkillService $skillService,
        private ProfileService $profileService
    ) {}

    /**
     * Get all available skills
     */
    public function index(Request $request)
    {
        try {
            $validated = $request->validate([
                'search' => 'nullable|string|max:255',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            $skills = $this->skillService->getAllSkills($validated);

            return response()->json([
                'success' => true,
                'data' => $skills->items(),
                'pagination' => [
                    'total' => $skills->total(),
                    'per_page' => $skills->perPage(),
                    'current_page' => $skills->currentPage(),
                    'last_page' => $skills->lastPage(),
                ],
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
    public function store(AddProviderSkillRequest $request)
    {
        try {
            $user = auth()->user();
            $this->skillService->addSkill($user, $request->validated()['skill_id']);

            Log::info('Skill added to provider', [
                'user_id' => $user->id,
                'skill_id' => $request->skill_id,
            ]);

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

    /**
     * Remove skill from provider profile
     */
    public function destroy($skillId)
    {
        try {
            $user = auth()->user();
            $this->skillService->removeSkill($user, $skillId);

            Log::info('Skill removed from provider', [
                'user_id' => $user->id,
                'skill_id' => $skillId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Skill removed successfully!',
                'data' => $this->profileService->getProfileWithSkills($user),
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Skill not found.',
            ], 404);

        } catch (Exception $e) {
            Log::error('Remove skill error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 422);
        }
    }
}
