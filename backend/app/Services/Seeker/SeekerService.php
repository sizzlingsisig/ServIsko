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
    private $disk = 'public'; // Use public disk

    public function getProfile(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'roles' => $user->getRoleNames(), // Spatie roles
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
     * Create provider profile with data
     */
    public function createProviderProfile(User $user, array $data = []): void
    {
            $user->providerProfile()->create();
    }

    /**
     * Upload profile picture
     */
        public function uploadProfilePicture(User $user, $file): array
    {
        if (!$file) {
            throw new Exception('No file provided.');
        }

        if ($file->getSize() > 2 * 1024 * 1024) {
            throw new Exception('Profile picture must be less than 2MB.');
        }

        $mimeType = $file->getMimeType();
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($mimeType, $allowedMimes)) {
            throw new Exception('Only JPEG, PNG, GIF, and WebP images are allowed.');
        }

        // Create directory if needed
        if (!Storage::disk($this->disk)->exists('profile_pictures')) {
            Storage::disk($this->disk)->makeDirectory('profile_pictures');
        }

        // Delete old file if exists
        if ($user->profile && $user->profile->profile_picture) {
            $oldPath = $user->profile->profile_picture;
            if (Storage::disk($this->disk)->exists($oldPath)) {
                Storage::disk($this->disk)->delete($oldPath);
            }
        }

        // Store new file
        $extension = $file->getClientOriginalExtension();
        $filename = "profile_pictures/{$user->id}.{$extension}";

        Storage::disk($this->disk)->putFileAs('profile_pictures', $file, "{$user->id}.{$extension}");

        \Log::info('Profile picture uploaded', [
            'filename' => $filename,
            'user_id' => $user->id,
        ]);

        // Ensure profile exists
        $this->createProfile($user);

        // Update user_profiles table with picture filename
        $user->profile()->update(['profile_picture' => $filename]);

        return $this->getProfile($user->fresh());
    }

   /**
 * Get profile picture content
 */
public function getProfilePicture(User $user)
{
    $user->refresh();

    if (!$user->profile || !$user->profile->profile_picture) {
        throw new Exception('Profile picture not found.');
    }

    $path = $user->profile->profile_picture;

    if (!Storage::disk($this->disk)->exists($path)) {
        throw new Exception('Profile picture not found.');
    }

    return Storage::disk($this->disk)->get($path);
}

/**
 * Get profile picture mime type
 */
public function getProfilePictureMimeType(User $user): string
{
    $user->refresh();

    if (!$user->profile || !$user->profile->profile_picture) {
        throw new Exception('Profile picture not found.');
    }

    $path = $user->profile->profile_picture;

    if (!Storage::disk($this->disk)->exists($path)) {
        throw new Exception('Profile picture not found.');
    }

    return Storage::disk($this->disk)->mimeType($path) ?? 'image/jpeg';
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
