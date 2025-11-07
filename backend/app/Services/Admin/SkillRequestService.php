<?php

namespace App\Services\Admin;

use App\Models\SkillRequest;
use App\Models\Skill;
use Carbon\Carbon;
use Exception;

class SkillRequestService
{
    /**
     * Get all skill requests with filters, search, and sorting
     */
    public function getAllRequests(array $filters = [])
    {
        $query = SkillRequest::with('user');

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Search by skill name or user name/email
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
        }

        // Sort by
        $sortBy = $filters['sort_by'] ?? 'created_at';
        switch ($sortBy) {
            case 'skill_name':
                $query->orderBy('name');
                break;
            case 'user_name':
                $query->join('users', 'skill_requests.user_id', '=', 'users.id')
                      ->orderBy('users.name')
                      ->select('skill_requests.*');
                break;
            default:
                $query->orderByDesc('created_at');
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    /**
     * Get skill request by ID
     */
    public function getRequestById(int $id): SkillRequest
    {
        return SkillRequest::with('user')->findOrFail($id);
    }

    /**
     * Approve a skill request
     */
    public function approveRequest(SkillRequest $skillRequest, array $data): SkillRequest
    {
        // Check if already processed
        if ($skillRequest->status !== 'pending') {
            throw new Exception("This request has already been {$skillRequest->status}.");
        }

        // Get the user
        $user = $skillRequest->user;

        // Create the skill or get existing one
        $skill = Skill::firstOrCreate(
            ['name' => strtolower($skillRequest->name)],
            [
                'name' => $skillRequest->name,
                'description' => $skillRequest->description,
            ]
        );

        // Check if user already has this skill
        if ($user->providerProfile &&
            $user->providerProfile->skills()->where('skill_id', $skill->id)->exists()) {
            throw new Exception('User already has this skill assigned.');
        }

        // Assign skill to user's provider profile
        if ($user->providerProfile) {
            $user->providerProfile->skills()->attach($skill->id);
        }

        // Update request status
        $skillRequest->update([
            'status' => 'approved',
            'reviewed_at' => Carbon::now(),
        ]);

        return $skillRequest->fresh();
    }

    /**
     * Reject a skill request
     */
    public function rejectRequest(SkillRequest $skillRequest, ?string $reason = null): SkillRequest
    {
        // Check if already processed
        if ($skillRequest->status !== 'pending') {
            throw new Exception("This request has already been {$skillRequest->status}.");
        }

        $skillRequest->update([
            'status' => 'rejected',
            'reviewed_at' => Carbon::now(),
        ]);

        return $skillRequest->fresh();
    }

    /**
     * Get statistics on skill requests
     */
    public function getStats(string $dateRange = 'all'): array
    {
        $query = SkillRequest::query();

        // Filter by date range
        switch ($dateRange) {
            case 'day':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'week':
                $query->whereBetween('created_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek(),
                ]);
                break;
            case 'month':
                $query->whereYear('created_at', Carbon::now()->year)
                      ->whereMonth('created_at', Carbon::now()->month);
                break;
        }

        $totalRequests = $query->count();
        $pending = (clone $query)->where('status', 'pending')->count();
        $approved = (clone $query)->where('status', 'approved')->count();
        $rejected = (clone $query)->where('status', 'rejected')->count();

        // Calculate approval rate
        $approvalRate = $totalRequests > 0 ? round(($approved / $totalRequests) * 100, 2) : 0;

        // Get most requested skills
        $mostRequestedSkills = (clone $query)
            ->selectRaw('LOWER(name) as skill_name')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('skill_name')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'skill_name' => $item->skill_name,
                    'count' => $item->count,
                ];
            })
            ->toArray();

        // Get requests by date
        $requestsByDate = (clone $query)
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved")
            ->selectRaw("SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected")
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending")
            ->groupBy('date')
            ->orderByDesc('date')
            ->limit(30)
            ->get()
            ->toArray();

        // Calculate average approval time
        $averageApprovalTime = (clone $query)
            ->where('status', 'approved')
            ->whereNotNull('reviewed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(DAY, created_at, reviewed_at)) as avg_days')
            ->first()
            ->avg_days ?? 0;

        return [
            'total_requests' => $totalRequests,
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
            'approval_rate' => $approvalRate . '%',
            'average_approval_time' => round($averageApprovalTime, 1) . ' days',
            'most_requested_skills' => $mostRequestedSkills,
            'requests_by_date' => $requestsByDate,
            'date_range' => $dateRange,
        ];
    }
}
