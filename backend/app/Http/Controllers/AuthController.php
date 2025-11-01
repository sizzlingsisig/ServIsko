<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Services\SeekerService;

use Exception;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService,
        private SeekerService $seekerService
    ) {}

    public function register(RegisterRequest $request, SeekerService $seekerService)
    {
        try {
            $result = DB::transaction(function () use ($request) {
                $result = $this->authService->register($request->validated());
                $this->seekerService->createProfile($result['user']);

                $result['user']->assignRole('service-seeker');
                return $result;
            });

            return response()->json([
                'success' => true,
                'message' => 'Registration successful!',
                'user' => [
                    'id' => $result['user']->id,
                    'username' => $result['user']->username,
                    'name' => $result['user']->name,
                    'email' => $result['user']->email,
                    'role' => 'service-seeker',
                ],
                'token' => $result['token'],
            ], 201);

        } catch (Exception $e) {
            Log::error('Registration error: ' . $e->getMessage(), [
                'email' => $request->email ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function login(LoginRequest $request)
    {
        $key = 'login:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'success' => false,
                'message' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ], 429);
        }

        try {
            $result = $this->authService->login($request->email, $request->password);

            RateLimiter::clear($key);

            return response()->json([
                'success' => true,
                'message' => 'Login successful!',
                'user' => [
                    'id' => $result['user']->id,
                    'username' => $result['user']->username,
                    'name' => $result['user']->name,
                    'email' => $result['user']->email,
                ],
                'token' => $result['token'],
            ], 200);

        } catch (ValidationException $e) {
            RateLimiter::hit($key, 300);
            throw $e;

        } catch (Exception $e) {
            Log::error('Login error: ' . $e->getMessage(), [
                'email' => $request->email ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Login failed. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $this->authService->logout($request->user());

            return response()->json([
                'success' => true,
                'message' => 'Logout successful!',
            ], 200);

        } catch (Exception $e) {
            Log::error('Logout error: ' . $e->getMessage(), [
                'user_id' => $request->user()->id ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Logout failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function logoutAll(Request $request)
    {
        try {
            $this->authService->logoutAll($request->user());

            return response()->json([
                'success' => true,
                'message' => 'Logged out from all devices successfully!',
            ], 200);

        } catch (Exception $e) {
            Log::error('Logout all error: ' . $e->getMessage(), [
                'user_id' => $request->user()->id ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Logout from all devices failed.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
