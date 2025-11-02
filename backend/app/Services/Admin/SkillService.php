<?php

namespace App\Services\Admin;

use App\Models\Skill;
use Illuminate\Pagination\LengthAwarePaginator;
use Exception;

class SkillService
{
    /**
     * Get all skills with pagination and search
     */
    public function getAllSkills(array $filters = []): LengthAwarePaginator
    {
        $query = Skill::query();

        // Search by name or description
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

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
        if (Skill::where('name', strtolower($data['name']))->exists()) {
            throw new Exception('A skill with this name already exists.');
        }

        return Skill::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
    }

    /**
     * Update skill
     */
    public function updateSkill(int $id, array $data): Skill
    {
        $skill = $this->getSkillById($id);

        // Check if new name already exists (excluding current skill)
        if (!empty($data['name']) && $data['name'] !== $skill->name) {
            if (Skill::where('name', strtolower($data['name']))
                     ->where('id', '!=', $id)
                     ->exists()) {
                throw new Exception('A skill with this name already exists.');
            }
        }

        $skill->update([
            'name' => $data['name'] ?? $skill->name,
            'description' => $data['description'] ?? $skill->description,
        ]);

        return $skill;
    }

    /**
     * Delete skill
     */
    public function deleteSkill(int $id): void
    {
        $skill = $this->getSkillById($id);

        // Check if skill is assigned to any provider profiles
        if ($skill->providerProfiles()->exists()) {
            throw new Exception('Cannot delete skill. It is currently assigned to providers.');
        }

        $skill->delete();
    }

    /**
     * Bulk create skills
     */
    public function bulkCreateSkills(array $skills): array
    {
        $created = [];

        foreach ($skills as $skillData) {
            try {
                $created[] = $this->createSkill($skillData);
            } catch (Exception $e) {
                // Log error but continue with other skills
                \Illuminate\Support\Facades\Log::warning(
                    "Failed to create skill: {$skillData['name']}: " . $e->getMessage()
                );
            }
        }

        if (empty($created)) {
            throw new Exception('No skills were created.');
        }

        return $created;
    }

    /**
     * Search skills
     */
    public function searchSkills(string $query = ''): array
    {
        if (empty($query)) {
            return [];
        }

        return Skill::where('name', 'like', "%{$query}%")
                    ->limit(20)
                    ->get()
                    ->toArray();
    }

    /**
     * Get popular skills (most assigned to providers)
     */
    public function getPopularSkills(int $limit = 10): array
    {
        return Skill::withCount('providerProfiles')
                    ->orderByDesc('provider_profiles_count')
                    ->limit($limit)
                    ->get()
                    ->toArray();
    }

    /**
     * Get provider count for skill
     */
    public function getProviderCount(int $skillId): int
    {
        $skill = $this->getSkillById($skillId);
        return $skill->providerProfiles()->count();
    }
}
