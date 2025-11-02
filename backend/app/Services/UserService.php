<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Exception;

class UserService
{

       /**
     * Get all users with filters
     */
    public function getAllUsers(array $filters = []): LengthAwarePaginator
    {
        $query = User::with('roles', 'profile', 'providerProfile');

        // Search by name, email, or username
        if (isset($filters['search']) && $filters['search']) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if (isset($filters['role']) && $filters['role']) {
            $query->role($filters['role']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    /**
     * Get user by ID
     */
    public function getUserById(int $id): User
    {
        return User::with('roles', 'profile', 'providerProfile')->findOrFail($id);
    }

    /**
     * Add role to user
     */
    public function addRoleToUser(User $user, string $role): void
    {
        if ($user->hasRole($role)) {
            throw new Exception('User already has this role.');
        }

        $user->assignRole($role);

        Log::info('Role added to user', [
            'user_id' => $user->id,
            'role' => $role,
        ]);

        // Create associated profile if needed
        $this->createAssociatedProfiles($user, $role);
    }

    /**
     * Remove role from user
     */
    public function removeRoleFromUser(User $user, string $role): void
    {
        if (!$user->hasRole($role)) {
            throw new Exception('User does not have this role.');
        }

        $user->removeRole($role);

        Log::info('Role removed from user', [
            'user_id' => $user->id,
            'role' => $role,
        ]);
    }

    /**
     * Assign service provider role and create provider profile
     */
    public function assignServiceProvider(User $user): User
    {
        // Check if already a provider
        if ($user->hasRole('service-provider')) {
            throw new Exception('User is already a service provider.');
        }

        // Assign provider role
        $user->assignRole('service-provider');

        Log::info('Provider role assigned to user', [
            'user_id' => $user->id,
        ]);

        // Create provider profile if it doesn't exist
        if (!$user->providerProfile) {
            $user->providerProfile()->create([
                'links' => [],
            ]);

            Log::info('Provider profile created', [
                'user_id' => $user->id,
            ]);
        }

        return $user->load('providerProfile');
    }

    /**
     * Delete user account
     */
    public function deleteUser(int $id): void
    {
        $user = User::findOrFail($id);
        $userId = $user->id;

        // Delete associated data
        if ($user->profile) {
            $user->profile->delete();
        }

        if ($user->providerProfile) {
            $user->providerProfile->delete();
        }

        // Delete user
        $user->delete();

        Log::info('User account deleted', ['user_id' => $userId]);
    }

    /**
     * Create associated profiles based on role
     */
    private function createAssociatedProfiles(User $user, string $role): void
    {
        if ($role === 'seeker' && !$user->profile) {
            $user->profile()->create([
                'bio' => null,
                'location' => null,
                'profile_picture' => null,
            ]);

            Log::info('Seeker profile created', ['user_id' => $user->id]);
        }

        if ($role === 'provider' && !$user->providerProfile) {
            $user->providerProfile()->create([
                'title' => null,
                'links' => [],
            ]);

            Log::info('Provider profile created', ['user_id' => $user->id]);
        }
    }

    /**
     * Get user statistics
     */
    public function getUserStats(): array
    {
        return [
            'total_users' => User::count(),
            'total_seekers' => User::role('seeker')->count(),
            'total_providers' => User::role('provider')->count(),
            'total_admins' => User::role('admin')->count(),
        ];
    }

    /**
     * Search users
     */
    public function searchUsers(string $query): array
    {
        return User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('username', 'like', "%{$query}%")
            ->limit(10)
            ->get()
            ->toArray();
    }
}
