<?php

namespace App\Services\Provider;

use App\Models\User;
use App\Models\Skill;
use Exception;

class SkillService
{
    /**
     * Get all available skills with pagination and search
     */
    public function getAllSkills(array $filters = [])
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
     * Add skill to provider
     */
    public function addSkill(User $user, int $skillId): void
    {
        $providerProfile = $user->providerProfile;

        if (!$providerProfile) {
            throw new Exception('Provider profile not found.');
        }

        // Check if skill exists
        Skill::findOrFail($skillId);

        // Check if skill already assigned
        if ($providerProfile->skills()->where('skill_id', $skillId)->exists()) {
            throw new Exception('This skill is already assigned.');
        }

        // Add skill
        $providerProfile->skills()->attach($skillId);
    }

    /**
     * Remove skill from provider
     */
    public function removeSkill(User $user, int $skillId): void
    {
        $providerProfile = $user->providerProfile;

        if (!$providerProfile) {
            throw new Exception('Provider profile not found.');
        }

        // Check if skill exists
        Skill::findOrFail($skillId);

        // Check if skill is assigned
        if (!$providerProfile->skills()->where('skill_id', $skillId)->exists()) {
            throw new Exception('This skill is not assigned to this provider.');
        }

        // Remove skill
        $providerProfile->skills()->detach($skillId);
    }

    /**
     * Get all skills for provider
     */
    public function getProviderSkills(User $user): array
    {
        $providerProfile = $user->providerProfile;

        if (!$providerProfile) {
            throw new Exception('Provider profile not found.');
        }

        return $providerProfile->skills()
                              ->get(['id', 'name', 'description'])
                              ->toArray();
    }
}
