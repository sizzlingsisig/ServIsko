<?php

namespace App\Services\Provider;

use App\Models\User;
use App\Models\SkillRequest;
use Exception;

class SkillRequestService
{
    /**
     * Create a skill request (request for new skill)
     */
    public function createRequest(User $user, array $data): SkillRequest
    {
        $providerProfile = $user->providerProfile;

        if (!$providerProfile) {
            throw new Exception('Provider profile not found.');
        }

        // Check if there's already a pending request with same skill name
        $existingRequest = SkillRequest::where('user_id', $user->id)
                                       ->whereRaw('LOWER(name) = ?', [strtolower($data['name'])])
                                       ->where('status', 'pending')
                                       ->first();

        if ($existingRequest) {
            throw new Exception('You already have a pending request for this skill.');
        }

        // Create skill request
        $skillRequest = SkillRequest::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => 'pending',
        ]);

        return $skillRequest;
    }

    /**
     * Get user's skill requests
     */
    public function getUserRequests(User $user, array $filters = [])
    {
        $query = SkillRequest::where('user_id', $user->id);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * Update skill request
     */
    public function updateRequest(User $user, $requestId, array $data): SkillRequest
    {
        $skillRequest = SkillRequest::where('user_id', $user->id)
            ->findOrFail($requestId);

        // Check if updating name to one that already exists as pending
        if (!empty($data['name']) && $data['name'] !== $skillRequest->name) {
            $existingRequest = SkillRequest::where('user_id', $user->id)
                ->whereRaw('LOWER(name) = ?', [strtolower($data['name'])])
                ->where('id', '!=', $requestId)
                ->where('status', 'pending')
                ->exists();

            if ($existingRequest) {
                throw new Exception('You already have a pending request for this skill.');
            }
        }

        $skillRequest->update($data);

        return $skillRequest;
    }

    /**
     * Delete skill request
     */
    public function deleteRequest(User $user, $requestId): void
    {
        $skillRequest = SkillRequest::where('user_id', $user->id)
            ->findOrFail($requestId);

        $skillRequest->delete();
    }

    /**
     * Get user's request stats
     */
    public function getUserRequestStats(User $user): array
    {
        $total = SkillRequest::where('user_id', $user->id)->count();
        $pending = SkillRequest::where('user_id', $user->id)
                              ->where('status', 'pending')
                              ->count();
        $approved = SkillRequest::where('user_id', $user->id)
                               ->where('status', 'approved')
                               ->count();
        $rejected = SkillRequest::where('user_id', $user->id)
                               ->where('status', 'rejected')
                               ->count();

        return [
            'total_requests' => $total,
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
        ];
    }

    /**
     * Check if user has pending request for skill name
     */
    public function hasPendingRequest(User $user, string $skillName): bool
    {
        return SkillRequest::where('user_id', $user->id)
                          ->whereRaw('LOWER(name) = ?', [strtolower($skillName)])
                          ->where('status', 'pending')
                          ->exists();
    }
}
