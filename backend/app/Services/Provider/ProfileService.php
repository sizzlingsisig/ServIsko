<?php

namespace App\Services\Provider;

use App\Models\User;
use App\Models\Skill;
use Exception;

class ProfileService
{
    /**
     * Get provider profile with skills and links
     */
    public function getProfileWithSkills(User $user): array
    {
        $providerProfile = $user->providerProfile;

        if (!$providerProfile) {
            throw new Exception('Provider profile not found.');
        }

        return [
            'profile' => [
                'id' => $providerProfile->id,
                'links' => $providerProfile->links()->orderBy('order')->get()->toArray(),
            ],
            'skills' => $providerProfile->skills()
                                       ->select('skills.id', 'skills.name', 'skills.description')
                                       ->get()
                                       ->toArray(),
            'services' => $user->services()->get()->toArray(),
            'user' => $user->only('id', 'name', 'email', 'username'),
        ];
    }

    /**
     * Get provider stats
     */
    public function getStats(User $user): array
    {
        $providerProfile = $user->providerProfile;

        if (!$providerProfile) {
            throw new Exception('Provider profile not found.');
        }

        $skillCount = $providerProfile->skills()->count();
        $linkCount = $providerProfile->links()->count();

        $totalFields = 3; // skills(2), links(1)
        $completedFields = ($skillCount > 0 ? 2 : 0) + ($linkCount > 0 ? 1 : 0);

        return [
            'total_skills' => $skillCount,
            'total_links' => $linkCount,
            'is_complete' => $skillCount > 0,
            'completion_percentage' => (int) round(($completedFields / $totalFields) * 100),
        ];
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

        Skill::findOrFail($skillId);

        if ($providerProfile->skills()->where('skill_id', $skillId)->exists()) {
            throw new Exception('This skill is already assigned.');
        }

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

        Skill::findOrFail($skillId);

        if (!$providerProfile->skills()->where('skill_id', $skillId)->exists()) {
            throw new Exception('This skill is not assigned to this provider.');
        }

        $providerProfile->skills()->detach($skillId);
    }
}
