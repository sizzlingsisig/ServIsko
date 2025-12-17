<?php

namespace App\Services\Admin;

use App\Models\User;
use Exception;

class UserService
{
    /**
     * Get all users with pagination, search, and role filtering
     */
    public function getAllUsers(array $filters = [])
    {
        $query = User::with(['roles', 'profile', 'providerProfile.links']);

        // Search by name or email
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
        }

        // Filter by role
        if (!empty($filters['role'])) {
            $query->role($filters['role']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    /**
     * Get user by ID with relationships
     */
    public function getUserById(int $id): User
    {
        return User::with(['roles', 'profile', 'providerProfile.links'])->findOrFail($id);
    }

    /**
     * Search users
     */
    public function searchUsers(string $query = ''): array
    {
        if (empty($query)) {
            return [];
        }

        return User::where('name', 'like', "%{$query}%")
                   ->orWhere('email', 'like', "%{$query}%")
                   ->orWhere('username', 'like', "%{$query}%")
                   ->limit(20)
                   ->get(['id', 'name', 'email', 'username'])
                   ->toArray();
    }

    /**
     * Get user statistics
     */
    public function getUserStats(): array
    {
        $totalUsers = User::count();
        $seekers = User::role('seeker')->count();
        $providers = User::role('service-provider')->count();
        $admins = User::role('admin')->count();

        return [
            'total_users' => $totalUsers,
            'seekers' => $seekers,
            'providers' => $providers,
            'admins' => $admins,
            'other' => $totalUsers - $seekers - $providers - $admins,
        ];
    }

    /**
     * Get users by role
     */
    public function getUsersByRole(string $role, int $limit = 10): array
    {
        return User::role($role)
                   ->with(['profile', 'providerProfile.links'])
                   ->limit($limit)
                   ->get()
                   ->toArray();
    }

    /**
     * Get recently created users
     */
    public function getRecentUsers(int $limit = 10): array
    {
        return User::latest('created_at')
                   ->with(['roles', 'profile', 'providerProfile.links'])
                   ->limit($limit)
                   ->get()
                   ->toArray();
    }

    /**
     * Get users with no roles
     */
    public function getUsersWithoutRoles(): array
    {
        return User::doesntHave('roles')
                   ->with('profile', 'providerProfile.links')
                   ->get()
                   ->toArray();
    }

    /**
     * Format user data
     */
    public function formatUserData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'roles' => $user->roles()->pluck('name')->toArray(),
            'profile' => $user->profile,
            'provider_profile' => $user->providerProfile ? [
                'id' => $user->providerProfile->id,
                'title' => $user->providerProfile->title,
                'links' => $user->providerProfile->links(),
                'skills' => $user->providerProfile->skills()
                    ->select('id', 'name', 'description')
                    ->get()
                    ->toArray(),
            ] : null,
        ];
    }
}
