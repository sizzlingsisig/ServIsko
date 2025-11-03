<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\SkillService;
use App\Http\Requests\Admin\CreateSkillRequest;
use App\Http\Requests\Admin\UpdateSkillRequest;
use App\Http\Requests\Admin\BulkCreateSkillRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class SkillController extends Controller
{
    public function __construct(private SkillService $skillService) {}

    /**
     * Get all skills
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
     * Get single skill
     */
    public function show($id)
    {
        try {
            $skill = $this->skillService->getSkillById($id);

            return response()->json([
                'success' => true,
                'data' => $skill,
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Skill not found.',
            ], 404);

        } catch (Exception $e) {
            Log::error('Get skill error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch skill.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Create new skill
     */
    public function store(CreateSkillRequest $request)
    {
        try {
            $skill = $this->skillService->createSkill($request->validated());

            Log::info('Skill created', ['skill_id' => $skill->id]);

            return response()->json([
                'success' => true,
                'message' => 'Skill created successfully!',
                'data' => $skill,
            ], 201);

        } catch (Exception $e) {
            Log::error('Create skill error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 422);
        }
    }

    /**
     * Update skill
     */
    public function update(UpdateSkillRequest $request, $id)
    {
        try {
            $skill = $this->skillService->updateSkill($id, $request->validated());

            Log::info('Skill updated', ['skill_id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Skill updated successfully!',
                'data' => $skill,
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

    /**
     * Delete skill
     */
    public function destroy($id)
    {
        try {
            $this->skillService->deleteSkill($id);

            Log::info('Skill deleted', ['skill_id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Skill deleted successfully!',
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Skill not found.',
            ], 404);

        } catch (Exception $e) {
            Log::error('Delete skill error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 422);
        }
    }

    /**
     * Bulk create skills
     */
    public function bulkCreate(BulkCreateSkillRequest $request)
    {
        try {
            $created = $this->skillService->bulkCreateSkills($request->validated()['skills']);

            Log::info('Skills bulk created', ['count' => count($created)]);

            return response()->json([
                'success' => true,
                'message' => count($created) . ' skill(s) created successfully!',
                'data' => $created,
            ], 201);

        } catch (Exception $e) {
            Log::error('Bulk create skills error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create skills.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 422);
        }
    }

    /**
     * Search skills
     */
    public function search(Request $request)
    {
        try {
            $validated = $request->validate([
                'q' => 'required|string|max:255',
            ]);

            $skills = $this->skillService->searchSkills($validated['q']);

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
}
