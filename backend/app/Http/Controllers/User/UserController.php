<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\User\UserService;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\UpdateUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class UserController extends Controller
{
    public function __construct(private UserService $userService) {}

    /**
     * Get authenticated user profile
     */
    public function show(Request $request)
    {
        try {
            $user = $this->userService->getUserInfo($request->user());

            return response()->json([
                'success' => true,
                'data' => $user,
            ], 200);

        } catch (Exception $e) {
            Log::error('Get user profile error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch profile.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update authenticated user profile
     */
    public function update(UpdateUserRequest $request)
    {
        try {
            $user = $request->user();
            $this->userService->updateProfile($user, $request->validated());

            Log::info('User updated', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully!',
                'data' => $user->only('name', 'username', 'email'),
            ], 200);

        } catch (Exception $e) {
            Log::error('Update user error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 422);
        }
    }

    /**
     * Change password
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $this->userService->changePassword(
                $request->user(),
                $request->validated()['current_password'],
                $request->validated()['new_password']
            );

            Log::info('Password changed', ['user_id' => $request->user()->id]);

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
     * Delete account
     */
    public function destroy(Request $request)
    {
        try {
            $userId = $request->user()->id;

            $this->userService->deleteAccount($request->user());

            Log::info('Account deleted', ['user_id' => $userId]);

            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully!',
            ], 200);

        } catch (Exception $e) {
            Log::error('Delete account error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
