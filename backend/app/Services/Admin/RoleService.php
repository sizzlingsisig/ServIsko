<?php

namespace App\Services\Admin;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Exception;

class RoleService
{
    /**
     * Add role to user
     */
    public function addRoleToUser(User $user, string $roleName): User
    {
        $role = Role::where('name', $roleName)->firstOrFail();

        // Check if user already has this role
        if ($user->hasRole($roleName)) {
            throw new Exception("User already has the {$roleName} role.");
        }

        $user->assignRole($role);

        return $user->fresh();
    }

    /**
     * Remove role from user
     */
    public function removeRoleFromUser(User $user, string $roleName): User
    {
        $role = Role::where('name', $roleName)->firstOrFail();

        // Check if user has this role
        if (!$user->hasRole($roleName)) {
            throw new Exception("User does not have the {$roleName} role.");
        }

        $user->removeRole($role);

        return $user->fresh();
    }

    /**
     * Assign service provider role and create provider profile
     */
    public function assignServiceProvider(User $user): User
    {
        // Check if user already is a service provider
        if ($user->hasRole('service-provider')) {
            throw new Exception('User is already a service provider.');
        }

        // Assign service provider role
        $this->addRoleToUser($user, 'service-provider');

        // Create provider profile if it doesn't exist
        if (!$user->providerProfile) {
            $user->providerProfile()->create([
                'bio' => null,
                'hourly_rate' => null,
                'availability' => 'available',
            ]);
        }

        return $user->load('roles', 'profile', 'providerProfile');
    }

    /**
     * Check if user has role
     */
    public function hasRole(User $user, string $roleName): bool
    {
        return $user->hasRole($roleName);
    }

    /**
     * Get user roles
     */
    public function getUserRoles(User $user): array
    {
        return $user->roles()->pluck('name')->toArray();
    }
}
