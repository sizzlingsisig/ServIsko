<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Services\Provider\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class ProfileController extends Controller
{
    public function __construct(
        private ProfileService $profileService
    ) {}

    /**
     * Get provider profile with skills and links
     */
    public function show()
    {
        try {
            $user = auth()->user();
            $data = $this->profileService->getProfileWithSkills($user);

            return response()->json([
                'success' => true,
                'data' => $data,
            ], 200);

        } catch (Exception $e) {
            Log::error('Get provider profile error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch provider profile.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get profile stats
     */
    public function stats()
    {
        try {
            $user = auth()->user();
            $stats = $this->profileService->getStats($user);

            return response()->json([
                'success' => true,
                'data' => $stats,
            ], 200);

        } catch (Exception $e) {
            Log::error('Get profile stats error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch profile stats.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
