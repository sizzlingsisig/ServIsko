<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthService;
use App\Services\Seeker\SeekerService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Exception;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService,
        private SeekerService $seekerService
    ) {}

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request)
    {
        try {
            $result = DB::transaction(function () use ($request) {
                // Register user
                $result = $this->authService->register($request->validated());

                // Create seeker profile
                $this->seekerService->createProfile($result['user']);

                // Assign seeker role
                $result['user']->assignRole('service-seeker');

                return $result;
            });

            Log::info('User registered successfully', [
                'user_id' => $result['user']->id,
                'email' => $result['user']->email,
            ]);

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
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request)
    {
        $key = 'login:' . $request->ip();

        // Check rate limit (10 attempts per 5 minutes)
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'success' => false,
                'message' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ], 429);
        }

        try {
            $result = $this->authService->login($request->email, $request->password);

            // Clear rate limit on successful login
            RateLimiter::clear($key);

            Log::info('User logged in successfully', [
                'user_id' => $result['user']->id,
                'email' => $result['user']->email,
            ]);

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
            // Increment rate limit on failed attempt
            RateLimiter::hit($key, 300);

            throw $e;

        } catch (Exception $e) {
            RateLimiter::hit($key, 300);

            Log::error('Login error: ' . $e->getMessage(), [
                'email' => $request->email ?? 'unknown',
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Login failed. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Logout user (revoke current token)
     */
    public function logout(Request $request)
    {
        try {
            $this->authService->logout($request->user());

            Log::info('User logged out', [
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Logout successful!',
            ], 200);

        } catch (Exception $e) {
            Log::error('Logout error: ' . $e->getMessage(), [
                'user_id' => $request->user()->id ?? 'unknown',
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Logout failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Logout from all devices (revoke all tokens)
     */
    public function logoutAll(Request $request)
    {
        try {
            $this->authService->logoutAll($request->user());

            Log::info('User logged out from all devices', [
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Logged out from all devices successfully!',
            ], 200);

        } catch (Exception $e) {
            Log::error('Logout all error: ' . $e->getMessage(), [
                'user_id' => $request->user()->id ?? 'unknown',
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Logout from all devices failed.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
