<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\SkillService;
use App\Http\Requests\CreateSkillRequest;
use App\Http\Requests\UpdateSkillRequest;
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
            $validated = $request->validated();

            $skill = $this->skillService->createSkill($validated);

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
            $validated = $request->validated();

            $skill = $this->skillService->updateSkill($id, $validated);

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
    public function bulkCreate(Request $request)
    {
        try {
            $validated = $request->validate([
                'skills' => 'required|array|min:1',
                'skills.*.name' => 'required|string|unique:skills,name',
                'skills.*.description' => 'nullable|string',
            ]);

            $created = $this->skillService->bulkCreateSkills($validated['skills']);

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
            $search = $request->query('q', '');

            $skills = $this->skillService->searchSkills($search);

            return response()->json([
                'success' => true,
                'data' => $skills,
            ], 200);

        } catch (Exception $e) {
            Log::error('Search skills error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 422);
        }
    }
}
