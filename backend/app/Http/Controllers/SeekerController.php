<?php

namespace App\Http\Controllers;

use App\Services\SeekerService;
use App\Http\Requests\UpdateUserProfileRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\UpdateProfilePictureRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class SeekerController extends Controller
{
    public function __construct(private SeekerService $seekerService) {}

    /**
     * Get authenticated seeker profile
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
    public function updateProfile(UpdateUserProfileRequest $request)
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
     * Change password
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $validated = $request->validated();

            $this->seekerService->changePassword(
                $request->user(),
                $validated['current_password'],
                $validated['new_password']
            );

            Log::info('Seeker password changed', ['user_id' => $request->user()->id]);

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully!',
            ], 200);

        } catch (Exception $e) {
            Log::error('Change password error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 400);
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
            $picture = $this->seekerService->getProfilePicture($request->user());

            return response($picture['content'], 200)
                ->header('Content-Type', $picture['mimeType']);

        } catch (Exception $e) {
            Log::error('Get profile picture error: ' . $e->getMessage());

            if ($e->getMessage() === 'Profile picture not found') {
                return response()->noContent();
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Delete account
     */
    public function destroy(Request $request)
    {
        try {
            $userId = $request->user()->id;

            $this->seekerService->deleteAccount($request->user());

            Log::info('Seeker account deleted', ['user_id' => $userId]);

            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully!',
            ], 200);

        } catch (Exception $e) {
            Log::error('Delete seeker account error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
