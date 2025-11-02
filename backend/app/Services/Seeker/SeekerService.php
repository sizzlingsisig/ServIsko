<?php

namespace App\Services\Seeker;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Exception;

class SeekerService
{
    /**
     * Get seeker profile
     */
    public function getProfile(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'profile' => $user->profile ? $user->profile->only(['bio', 'location', 'phone']) : null,
            'has_profile_picture' => Storage::exists("profile_pictures/{$user->id}"),
        ];
    }

    /**
     * Update seeker profile
     */
    public function updateProfile(User $user, array $data): array
    {

        // Update or create seeker profile
        if (isset($data['bio']) || isset($data['location']) || isset($data['phone'])) {
            if ($user->profile) {
                $user->profile->update([
                    'bio' => $data['bio'] ?? $user->profile->bio,
                    'location' => $data['location'] ?? $user->profile->location,
                    'phone' => $data['phone'] ?? $user->profile->phone,
                ]);
            } else {
                $user->profile()->create([
                    'bio' => $data['bio'] ?? null,
                    'location' => $data['location'] ?? null,
                    'phone' => $data['phone'] ?? null,
                ]);
            }
        }

        return $this->getProfile($user->fresh());
    }

    /**
     * Create seeker profile
     */
    public function createProfile(User $user): void
    {
        if (!$user->profile) {
            $user->profile()->create([
                'bio' => null,
                'location' => null,
                'phone' => null,
            ]);
        }
    }

    /**
     * Upload profile picture
     */
    public function uploadProfilePicture(User $user, $file): array
    {
        if (!$file) {
            throw new Exception('No file provided.');
        }

        // Validate file
        if ($file->getSize() > 10 * 1024 * 1024) { // 10MB limit
            throw new Exception('Profile picture must be less than 5MB.');
        }

        $mimeType = $file->getMimeType();
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($mimeType, $allowedMimes)) {
            throw new Exception('Only JPEG, PNG, GIF, and WebP images are allowed.');
        }

        // Delete old profile picture if exists
        $path = "profile_pictures/{$user->id}";
        if (Storage::exists($path)) {
            Storage::delete($path);
        }

        // Store new profile picture
        Storage::put($path, file_get_contents($file));

        return $this->getProfile($user);
    }

    /**
     * Get profile picture
     */
    public function getProfilePicture(User $user): array
    {
        $path = "profile_pictures/{$user->id}";

        if (!Storage::exists($path)) {
            throw new Exception('Profile picture not found.');
        }

        $content = Storage::get($path);
        $mimeType = Storage::mimeType($path);

        return [
            'content' => $content,
            'mimeType' => $mimeType,
        ];
    }

    /**
     * Delete profile picture
     */
    public function deleteProfilePicture(User $user): void
    {
        $path = "profile_pictures/{$user->id}";

        if (Storage::exists($path)) {
            Storage::delete($path);
        }
    }

    /**
     * Check if seeker has profile picture
     */
    public function hasProfilePicture(User $user): bool
    {
        return Storage::exists("profile_pictures/{$user->id}");
    }
}
