<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class UserController extends Controller
{
    public function __construct(private UserService $userService) {}

    /**
     * Get all users (Admin)
     */
    public function index(Request $request)
    {
        try {
            $validated = $request->validate([
                'per_page' => 'nullable|integer|min:1|max:100',
                'search' => 'nullable|string|max:255',
                'role' => 'nullable|string|exists:roles,name',
            ]);

            $users = $this->userService->getAllUsers($validated);

            return response()->json([
                'success' => true,
                'data' => $users->items(),
                'pagination' => [
                    'total' => $users->total(),
                    'per_page' => $users->perPage(),
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                ],
            ], 200);

        } catch (Exception $e) {
            Log::error('Get users error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve users.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get specific user (Admin)
     */
    public function show($id)
    {
        try {
            $user = $this->userService->getUserById($id);

            return response()->json([
                'success' => true,
                'data' => $user,
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);

        } catch (Exception $e) {
            Log::error('Get user error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Add a role to user (Admin)
     */
    public function addRole(Request $request, $userId)
    {
        try {
            $validated = $request->validate([
                'role' => 'required|string|exists:roles,name',
            ]);

            $user = $this->userService->getUserById($userId);

            $this->userService->addRoleToUser($user, $validated['role']);

            return response()->json([
                'success' => true,
                'message' => 'Role added successfully!',
                'data' => $user->load('roles', 'profile', 'providerProfile'),
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);

        } catch (Exception $e) {
            Log::error('Add role error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 409);
        }
    }

    /**
     * Assign service provider role (Admin)
     */
    public function assignServiceProvider(Request $request, $userId)
    {
        try {
            $user = $this->userService->getUserById($userId);

            $updatedUser = $this->userService->assignServiceProvider($user);

            return response()->json([
                'success' => true,
                'message' => 'Service provider role assigned successfully!',
                'data' => $updatedUser,
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);

        } catch (Exception $e) {
            Log::error('Assign service provider error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 409);
        }
    }
}
