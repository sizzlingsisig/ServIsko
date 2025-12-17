<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Exception;

class UserService
{
    /**
     * Update user profile (name, username, email)
     */
    public function updateProfile(User $user, array $data): User
    {
        // Only update allowed fields
        $updateData = [];

        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }

        if (isset($data['username'])) {
            $updateData['username'] = $data['username'];
        }

        if (isset($data['email'])) {
            $updateData['email'] = $data['email'];
        }

        if (empty($updateData)) {
            throw new Exception('No valid fields to update.');
        }

        $user->update($updateData);

        return $user;
    }

    /**
     * Change user password
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): void
    {
        // Verify current password
        if (!Hash::check($currentPassword, $user->password)) {
            throw new Exception('Current password is incorrect.');
        }

        // Prevent using same password
        if (Hash::check($newPassword, $user->password)) {
            throw new Exception('New password must be different from current password.');
        }

        // Update password and logout all devices
        $user->update(['password' => Hash::make($newPassword)]);
        $user->tokens()->delete();
    }

    /**
     * Delete user account
     */
    public function deleteAccount(User $user): void
    {
        // Delete user profile if exists
        if ($user->profile) {
            $user->profile->delete();
        }

        // Delete provider profile if exists
        if ($user->providerProfile) {
            // Detach all skills
            $user->providerProfile->skills()->detach();
            $user->providerProfile->delete();
        }

        // Delete all tokens
        $user->tokens()->delete();

        // Revoke all roles
        $user->syncRoles([]);

        // Delete user account
        $user->delete();
    }

    /**
     * Get user basic info with eager loaded relationships
     */
    public function getUserInfo(User $user): array
    {
        // Ensure relationships are loaded
        if (!$user->relationLoaded('roles')) {
            $user->load('roles');
        }
        if (!$user->relationLoaded('profile')) {
            $user->load('profile');
        }
        if (!$user->relationLoaded('providerProfile')) {
            $user->load('providerProfile.links', 'providerProfile.skills');
        }

        $providerProfile = $user->providerProfile;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'roles' => $user->roles->pluck('name')->toArray(),
            'profile' => $user->profile?->toArray(),
            'provider_profile' => $providerProfile ? [
                'id' => $providerProfile->id,
                'bio' => $providerProfile->bio,
                'location' => $providerProfile->location,
                'rating' => $providerProfile->rating ?? 0,
                'total_reviews' => $providerProfile->total_reviews ?? 0,
                'is_verified' => $providerProfile->is_verified ?? false,
                'links' => $providerProfile->links->toArray(),
                'skills' => $providerProfile->skills->map(fn($skill) => [
                    'id' => $skill->id,
                    'name' => $skill->name,
                    'description' => $skill->description,
                ])->toArray(),
            ] : null,
        ];
    }

    /**
     * Check if email is verified
     */
    public function isEmailVerified(User $user): bool
    {
        return $user->isEmailVerified();
    }

    /**
     * Verify email
     */
    public function verifyEmail(User $user): void
    {
        if (!$user->isEmailVerified()) {
            $user->update(['email_verified_at' => now()]);
        }
    }

    /**
     * Check if user has role
     */
    public function hasRole(User $user, string $role): bool
    {
        return $user->hasRole($role);
    }
}
