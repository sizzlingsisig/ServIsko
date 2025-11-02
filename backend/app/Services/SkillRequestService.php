<?php

namespace App\Services;

use App\Models\SkillRequest;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Exception;

class SkillRequestService
{
    /**
     * Create skill request
     */
    public function createRequest(User $user, array $data): SkillRequest
    {
        // Check if pending request exists
        $exists = SkillRequest::where('user_id', $user->id)
            ->where('name', strtolower($data['name']))
            ->where('status', 'pending')
            ->exists();

        if ($exists) {
            throw new Exception('You already have a pending request for this skill.');
        }

        $request = SkillRequest::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'reason' => $data['reason'],
            'status' => 'pending',
        ]);

        Log::info('Skill request created', [
            'user_id' => $user->id,
            'skill_name' => $data['name'],
        ]);

        return $request;
    }

    /**
     * Get user's requests
     */
    public function getUserRequests(User $user)
    {
        return SkillRequest::where('user_id', $user->id)
            ->newest()
            ->get();
    }

    /**
     * Get all requests (admin)
     */
    public function getAllRequests(array $filters = []): LengthAwarePaginator
    {
        $query = SkillRequest::with('user');

        if (isset($filters['status']) && $filters['status']) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search']) && $filters['search']) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        return $query->newest()->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get single request (admin)
     */
    public function getRequest(int $requestId): SkillRequest
    {
        return SkillRequest::with('user')->findOrFail($requestId);
    }

    /**
     * Approve request (admin)
     */
    public function approveRequest(int $requestId): Skill
    {
        $skillRequest = SkillRequest::findOrFail($requestId);

        // Check if already processed
        if ($skillRequest->status !== 'pending') {
            throw new Exception("Request status is {$skillRequest->status}. Cannot approve.");
        }

        // Check if skill already exists
        $existingSkill = Skill::where('name', strtolower($skillRequest->name))->first();
        if ($existingSkill) {
            throw new Exception('Skill already exists in database.');
        }

        // Create skill
        $skill = Skill::create([
            'name' => $skillRequest->name,
            'description' => $skillRequest->description,
            'is_active' => true,
        ]);

        // Update request
        $skillRequest->update([
            'status' => 'approved',
            'reviewed_at' => now(),
        ]);

        Log::info('Skill request approved', [
            'request_id' => $requestId,
            'skill_id' => $skill->id,
            'skill_name' => $skill->name,
            'admin_id' => auth()->id(),
        ]);

        return $skill;
    }

    /**
     * Reject request (admin)
     */
    public function rejectRequest(int $requestId, string $reason): void
    {
        $skillRequest = SkillRequest::findOrFail($requestId);

        // Check if already processed
        if ($skillRequest->status !== 'pending') {
            throw new Exception("Request status is {$skillRequest->status}. Cannot reject.");
        }

        $skillRequest->update([
            'status' => 'rejected',
            'admin_notes' => $reason,
            'reviewed_at' => now(),
        ]);

        Log::info('Skill request rejected', [
            'request_id' => $requestId,
            'admin_id' => auth()->id(),
        ]);
    }

    /**
     * Get statistics
     */
    public function getStats(): array
    {
        return [
            'pending' => SkillRequest::pending()->count(),
            'approved' => SkillRequest::approved()->count(),
            'rejected' => SkillRequest::rejected()->count(),
            'total' => SkillRequest::count(),
        ];
    }
}
