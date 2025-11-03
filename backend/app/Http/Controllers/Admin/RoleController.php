<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\RoleService;
use App\Http\Requests\Admin\AddRoleRequest;
use App\Http\Requests\Admin\RemoveRoleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class RoleController extends Controller
{
    public function __construct(private RoleService $roleService) {}

    /**
     * Add a role to user (Admin)
     */
    public function addRole(AddRoleRequest $request, $userId)
    {
        try {
            $user = $this->getUserById($userId);

            $this->roleService->addRoleToUser($user, $request->validated()['role']);

            Log::info('Role added to user', [
                'user_id' => $userId,
                'role' => $request->validated()['role'],
            ]);

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
            $user = $this->getUserById($userId);

            $updatedUser = $this->roleService->assignServiceProvider($user);

            Log::info('Service provider role assigned', ['user_id' => $userId]);

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

    /**
     * Remove role from user (Admin)
     */
    public function removeRole(RemoveRoleRequest $request, $userId)
    {
        try {
            $user = $this->getUserById($userId);

            $this->roleService->removeRoleFromUser($user, $request->validated()['role']);

            Log::info('Role removed from user', [
                'user_id' => $userId,
                'role' => $request->validated()['role'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Role removed successfully!',
                'data' => $user->load('roles', 'profile', 'providerProfile'),
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);

        } catch (Exception $e) {
            Log::error('Remove role error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 409);
        }
    }

    /**
     * Helper method to get user by ID
     */
    private function getUserById($userId)
    {
        return \App\Models\User::findOrFail($userId);
    }
}
