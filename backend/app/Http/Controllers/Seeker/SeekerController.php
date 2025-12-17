<?php

namespace App\Http\Controllers\Seeker;

use App\Http\Controllers\Controller;
use App\Services\Seeker\SeekerService;
use App\Http\Requests\Seeker\UpdateProfileRequest;
use App\Http\Requests\Seeker\UpdateProfilePictureRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class SeekerController extends Controller
{
    public function __construct(private SeekerService $seekerService) {}

    /**
     * Get seeker profile
     */
    public function show(Request $request)
    {
        try {
            $user = $this->seekerService->getProfile($request->user());

            return response()->json([
                'success' => true,
                'data' => $user,
            ], 200);

        } catch (Exception $e) {
            Log::error('Get seeker profile error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch profile.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update seeker profile
     */
    public function update(UpdateProfileRequest $request)
    {
        try {
            $user = $this->seekerService->updateProfile(
                $request->user(),
                $request->validated()
            );

            Log::info('Seeker profile updated', ['user_id' => $request->user()->id]);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully!',
                'data' => $user,
            ], 200);

        } catch (Exception $e) {
            Log::error('Update seeker profile error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Upload profile picture
     */
    public function uploadProfilePicture(UpdateProfilePictureRequest $request)
    {
        try {
            $user = $this->seekerService->uploadProfilePicture(
                $request->user(),
                $request->file('profile_picture')
            );

            Log::info('Profile picture uploaded', ['user_id' => $request->user()->id]);

            return response()->json([
                'success' => true,
                'message' => 'Profile picture uploaded successfully!',
                'data' => $user,
            ], 201);

        } catch (Exception $e) {
            Log::error('Upload profile picture error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload profile picture.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
 * Get profile picture
 */
public function getProfilePicture(Request $request)
{
    try {
        $content = $this->seekerService->getProfilePicture($request->user());
        $mimeType = $this->seekerService->getProfilePictureMimeType($request->user());

        return response($content, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline');

    } catch (Exception $e) {
        Log::error('Get profile picture error: ' . $e->getMessage());
        return response()->noContent(204);
    }
}


    /**
     * Delete profile picture
     */
    public function deleteProfilePicture(Request $request)
    {
        try {
            $this->seekerService->deleteProfilePicture($request->user());

            Log::info('Profile picture deleted', ['user_id' => $request->user()->id]);

            return response()->json([
                'success' => true,
                'message' => 'Profile picture deleted successfully!',
            ], 200);

        } catch (Exception $e) {
            Log::error('Delete profile picture error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete profile picture.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
