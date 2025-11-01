<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

class SeekerService
{
    public function createProfile(User $user)
    {
        return $user->profile()->create([
            'profile_picture' => null,
            'bio' => null,
            'location' => null,
        ]);
    }
    /**
     * Get seeker profile with all data
     */
    public function getProfile(User $user)
    {
        return $user->load('profile');
    }

    /**
     * Update seeker profile
     */
    public function updateProfile(User $user, array $data)
    {
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'bio' => $data['bio'] ?? null,
                'location' => $data['location'] ?? null,
            ]
        );

        return $user->load('profile');
    }

    /**
     * Change user password
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword)
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw new Exception('Current password is incorrect');
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);
    }

    /**
     * Upload profile picture
     */
   public function uploadProfilePicture(User $user, $file)
{
    if (!$file) {
        throw new Exception('File not provided');
    }

    Log::info('Starting profile picture upload', [
        'user_id' => $user->id,
        'original_name' => $file->getClientOriginalName(),
        'size' => $file->getSize(),
        'mime_type' => $file->getMimeType(),
    ]);

    // Reload profile fresh from database
    $user->load('profile');

    // Delete old picture if exists
    if ($user->profile?->profile_picture) {
        $oldPath = $user->profile->profile_picture;

        if (Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
            Log::info('Deleted old profile picture', [
                'user_id' => $user->id,
                'path' => $oldPath,
            ]);
        }
    }

    $extension = $file->getClientOriginalExtension();
    $filename = $user->id . '_profilePicture.' . $extension;

    try {
        $path = Storage::disk('public')->putFileAs(
            'profile_pictures',
            $file,
            $filename,
            'public'
        );

        Log::info('Profile picture stored successfully', [
            'user_id' => $user->id,
            'path' => $path,
            'filename' => $filename,
        ]);

    } catch (Exception $e) {
        Log::error('Profile picture storage failed', [
            'user_id' => $user->id,
            'error' => $e->getMessage(),
        ]);
        throw new Exception('Failed to store profile picture: ' . $e->getMessage());
    }

    // Ensure profile exists
    if (!$user->profile) {
        $user->profile()->create([
            'profile_picture' => null,
            'bio' => null,
            'location' => null,
        ]);
    }

    $user->profile()->update([
        'profile_picture' => $path
    ]);

    Log::info('Profile updated with picture', [
        'user_id' => $user->id,
        'profile_picture_path' => $path,
    ]);

    return $user->load('profile');
}


    /**
     * Get profile picture
     */
    public function getProfilePicture(User $user)
    {
        if (!$user->profile?->profile_picture) {
            throw new Exception('Profile picture not found');
        }

        $path = $user->profile->profile_picture;

        Log::info('Retrieving profile picture', [
            'user_id' => $user->id,
            'path' => $path,
        ]);

        if (!Storage::disk('public')->exists($path)) {
            Log::error('Profile picture file does not exist', [
                'user_id' => $user->id,
                'path' => $path,
            ]);
            throw new Exception('Profile picture file not found');
        }

        return [
            'content' => Storage::disk('public')->get($path),
            'mimeType' => Storage::disk('public')->mimeType($path),
        ];
    }

    /**
     * Delete profile picture
     */
    public function deleteProfilePicture(User $user)
    {
        if (!$user->profile?->profile_picture) {
            throw new Exception('Profile picture not found');
        }

        $this->deleteOldProfilePicture($user);
        $user->profile()->update(['profile_picture' => null]);
    }

    /**
     * Delete seeker account
     */
    public function deleteAccount(User $user)
    {
        $this->deleteOldProfilePicture($user);
        $user->profile?->delete();
        $user->delete();
    }

    /**
     * Helper: Delete old profile picture from storage
     */
    private function deleteOldProfilePicture(User $user): void
    {
        if ($user->profile?->profile_picture) {
            Log::info('Deleting old profile picture', [
                'user_id' => $user->id,
                'path' => $user->profile->profile_picture,
            ]);

            if (Storage::disk('public')->exists($user->profile->profile_picture)) {
                Storage::disk('public')->delete($user->profile->profile_picture);
                Log::info('Old profile picture deleted', [
                    'user_id' => $user->id,
                ]);
            }
        }
    }
}
