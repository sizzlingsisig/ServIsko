<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\UserService;
use App\Http\Requests\Admin\GetUsersRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class UserController extends Controller
{
    public function __construct(private UserService $userService) {}

    /**
     * Get all users (Admin)
     */
    public function index(GetUsersRequest $request)
    {
        try {
            $users = $this->userService->getAllUsers($request->validated());

            // Transform response to include skills for providers
            $data = $users->items();
            $data = array_map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'roles' => $user->roles()->pluck('name')->toArray(),
                    'profile' => $user->profile,
                    'provider_profile' => $user->providerProfile ? [
                        'id' => $user->providerProfile->id,
                        'title' => $user->providerProfile->title,
                        'links' => $user->providerProfile->links->toArray(),
                        'skills' => $user->providerProfile->skills()
                            ->get(['skills.id', 'skills.name', 'skills.description'])
                            ->toArray(),
                    ] : null,
                ];
            }, $data);

            return response()->json([
                'success' => true,
                'data' => $data,
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

            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'roles' => $user->roles()->pluck('name')->toArray(),
                'profile' => $user->profile,
                'provider_profile' => $user->providerProfile ? [
                    'id' => $user->providerProfile->id,
                    'title' => $user->providerProfile->title,
                    'links' => $user->providerProfile->links->toArray(),
                    'skills' => $user->providerProfile->skills()
                        ->get(['skills.id', 'skills.name', 'skills.description'])
                        ->toArray(),
                ] : null,
            ];

            return response()->json([
                'success' => true,
                'data' => $userData,
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
}
