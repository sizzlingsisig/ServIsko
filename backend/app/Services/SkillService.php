<?php

namespace App\Services;

use App\Models\Skill;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Exception;

class SkillService
{
    /**
     * Get all skills with search and pagination
     */
        public function getAllSkills(array $filters = []): LengthAwarePaginator
    {
        $query = Skill::query();

        // Search
        if (isset($filters['search']) && $filters['search']) {
            $query->search($filters['search']);
        }

        // Ordering
        $query->orderBy('id', 'asc');  // ← Changed from ordered()

        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }


    /**
     * Get skill by ID
     */
    public function getSkillById(int $id): Skill
    {
        return Skill::findOrFail($id);
    }

    /**
     * Create new skill
     */
    public function createSkill(array $data): Skill
    {
        // Check if skill already exists
        $exists = Skill::where('name', strtolower($data['name']))->exists();
        if ($exists) {
            throw new Exception('Skill with this name already exists.');
        }

        $skill = Skill::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        Log::info('Skill created', ['skill_id' => $skill->id, 'name' => $skill->name]);

        return $skill;
    }

    /**
     * Update skill
     */
    public function updateSkill(int $id, array $data): Skill
    {
        $skill = Skill::findOrFail($id);

        // Check if another skill with same name exists
        $exists = Skill::where('name', strtolower($data['name']))
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            throw new Exception('Skill with this name already exists.');
        }

        $skill->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? $skill->description,
        ]);

        Log::info('Skill updated', ['skill_id' => $skill->id, 'name' => $skill->name]);

        return $skill;
    }

    /**
     * Delete skill
     */
    public function deleteSkill(int $id): void
    {
        $skill = Skill::findOrFail($id);

        // Check if skill is being used
        $providerCount = $skill->providerProfiles()->count();
        if ($providerCount > 0) {
            throw new Exception("Cannot delete skill. It is being used by {$providerCount} provider profile(s).");
        }

        $skillName = $skill->name;
        $skill->delete();

        Log::info('Skill deleted', ['skill_id' => $id, 'name' => $skillName]);
    }

    /**
     * Bulk create skills
     */
    public function bulkCreateSkills(array $skillsData): array
    {
        $created = [];

        foreach ($skillsData as $skillData) {
            $skill = Skill::create($skillData);
            $created[] = $skill;
        }

        Log::info('Skills bulk created', ['count' => count($created)]);

        return $created;
    }

    /**
     * Search skills
     */
    public function searchSkills(string $query): array
{
    if (empty($query)) {
        throw new Exception('Search query is required.');
    }

    return Skill::search($query)
        ->orderBy('name', 'asc')  // ← Changed from ordered()
        ->get()
        ->toArray();
}
}
