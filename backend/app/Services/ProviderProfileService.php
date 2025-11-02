<?php

namespace App\Services;

use App\Models\User;
use App\Models\Skill;
use Illuminate\Support\Facades\Log;
use Exception;

class ProviderProfileService
{
    /**
     * Get provider profile with skills
     */
    public function getProfileWithSkills(User $provider)
    {
        if (!$provider->hasRole('service-provider')) {
            throw new Exception('User is not a service provider.');
        }

        return $provider->load('providerProfile.skills');
    }

    /**
     * Update provider profile (title)
     */
    public function updateProfile(User $provider, array $data): void
    {
        // Ensure profile exists
        if (!$provider->providerProfile) {
            $provider->providerProfile()->create([
                'title' => null,
                'links' => [],
            ]);
        }

        $provider->providerProfile()->update([
            'title' => $data['title'] ?? null,
        ]);

        Log::info('Provider profile updated', ['user_id' => $provider->id]);
    }

    /**
     * Add link to provider profile
     */
    public function addLink(User $provider, array $data): void
    {
        // Ensure profile exists
        if (!$provider->providerProfile) {
            $provider->providerProfile()->create([
                'title' => null,
                'links' => [],
            ]);
        }

        $links = $provider->providerProfile->links ?? [];

        $links[] = [
            'id' => uniqid(),
            'title' => $data['title'],
            'url' => $data['url'],
            'created_at' => now()->toIso8601String(),
        ];

        $provider->providerProfile()->update(['links' => $links]);

        Log::info('Link added to provider profile', [
            'user_id' => $provider->id,
            'title' => $data['title'],
        ]);
    }

    /**
     * Update link in provider profile
     */
    public function updateLink(User $provider, string $linkId, array $data): void
    {
        if (!$provider->providerProfile) {
            throw new Exception('Provider profile not found.');
        }

        $links = $provider->providerProfile->links ?? [];
        $found = false;

        foreach ($links as &$link) {
            if ($link['id'] === $linkId) {
                $link['title'] = $data['title'];
                $link['url'] = $data['url'];
                $link['updated_at'] = now()->toIso8601String();
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new Exception('Link not found.');
        }

        $provider->providerProfile()->update(['links' => array_values($links)]);

        Log::info('Link updated in provider profile', [
            'user_id' => $provider->id,
            'link_id' => $linkId,
        ]);
    }

    /**
     * Remove link from provider profile
     */
    public function removeLink(User $provider, string $linkId): void
    {
        if (!$provider->providerProfile) {
            throw new Exception('Provider profile not found.');
        }

        $links = $provider->providerProfile->links ?? [];
        $initialCount = count($links);

        $links = array_filter($links, fn($link) => $link['id'] !== $linkId);

        if (count($links) === $initialCount) {
            throw new Exception('Link not found.');
        }

        $provider->providerProfile()->update(['links' => array_values($links)]);

        Log::info('Link removed from provider profile', [
            'user_id' => $provider->id,
            'link_id' => $linkId,
        ]);
    }

    /**
     * Get all available skills
     */
    public function getAllSkills(array $filters = [])
    {
        $query = Skill::where('is_active', true);

        // Sorting
        $sort = $filters['sort'] ?? 'name';
        $query->orderBy($sort, 'asc');

        return $query->get();
    }

    /**
     * Search skills
     */
    public function searchSkills(string $query)
    {
        if (empty($query)) {
            throw new Exception('Search query is required.');
        }

        return Skill::where('is_active', true)
            ->search($query)
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Add skill to provider profile
     */
    public function addSkill(User $provider, int $skillId): void
    {
        // Verify skill exists
        $skill = Skill::findOrFail($skillId);

        if (!$skill->is_active) {
            throw new Exception('This skill is no longer available.');
        }

        // Ensure profile exists
        if (!$provider->providerProfile) {
            $provider->providerProfile()->create([
                'title' => null,
                'links' => [],
            ]);
        }

        // Check if skill already attached
        if ($provider->providerProfile->skills()->where('skill_id', $skillId)->exists()) {
            throw new Exception('This skill is already added.');
        }

        // Attach skill with default proficiency
        $provider->providerProfile()->skills()->attach($skillId, [
            'proficiency_level' => 'beginner',
            'years_of_experience' => 0,
        ]);

        Log::info('Skill added to provider profile', [
            'user_id' => $provider->id,
            'skill_id' => $skillId,
            'skill_name' => $skill->name,
        ]);
    }

    /**
     * Update skill in provider profile
     */
    public function updateSkill(User $provider, int $skillId, array $data): void
    {
        // Verify skill exists
        Skill::findOrFail($skillId);

        if (!$provider->providerProfile) {
            throw new Exception('Provider profile not found.');
        }

        // Check if skill exists in profile
        if (!$provider->providerProfile->skills()->where('skill_id', $skillId)->exists()) {
            throw new Exception('Skill not found in profile.');
        }

        // Update pivot table with proficiency data
        $provider->providerProfile()->skills()->updateExistingPivot($skillId, [
            'proficiency_level' => $data['proficiency_level'],
            'years_of_experience' => $data['years_of_experience'] ?? 0,
        ]);

        Log::info('Skill updated in provider profile', [
            'user_id' => $provider->id,
            'skill_id' => $skillId,
            'proficiency_level' => $data['proficiency_level'],
        ]);
    }

    /**
     * Remove skill from provider profile
     */
    public function removeSkill(User $provider, int $skillId): void
    {
        if (!$provider->providerProfile) {
            throw new Exception('Provider profile not found.');
        }

        // Check if skill exists in profile
        if (!$provider->providerProfile->skills()->where('skill_id', $skillId)->exists()) {
            throw new Exception('Skill not found in profile.');
        }

        // Detach skill from pivot table
        $provider->providerProfile()->skills()->detach($skillId);

        Log::info('Skill removed from provider profile', [
            'user_id' => $provider->id,
            'skill_id' => $skillId,
        ]);
    }
}
