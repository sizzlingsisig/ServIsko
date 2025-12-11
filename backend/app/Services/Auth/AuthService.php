<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Exception;

class AuthService
{
    /**
     * Register a new user
     */
    public function register(array $data): array
    {
        // Check if user already exists
        if (User::where('email', $data['email'])->orWhere('username', $data['username'])->exists()) {
            throw new Exception('Email or username already exists.');
        }

        // Create user
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'email_verified_at' => null,
        ]);

        $user ->assignRole('service-seeker');

        // Generate API token
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Login user
     */
    public function login(string $email, string $password): array
    {
        // Find user by email
        $user = User::where('email', $email)->first();

        // Check if user exists and password is correct
        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if user is active
        if ($user->status === 'inactive') {
            throw new Exception('Your account has been deactivated. Please contact support.');
        }

        // Generate API token
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user->load('roles'),
            'token' => $token,
        ];
    }

    /**
     * Logout user (revoke current token)
     */
    public function logout(User $user): void
    {
        // Revoke current token
        $user->currentAccessToken()->delete();
    }

    /**
     * Logout from all devices (revoke all tokens)
     */
    public function logoutAll(User $user): void
    {
        // Revoke all tokens
        $user->tokens()->delete();
    }

    /**
     * Verify email
     */
    public function verifyEmail(User $user): void
    {
        if (!$user->email_verified_at) {
            $user->update(['email_verified_at' => now()]);
        }
    }

    /**
     * Check if email is verified
     */
    public function isEmailVerified(User $user): bool
    {
        return !is_null($user->email_verified_at);
    }

    /**
     * Update user password
     */
    public function updatePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw new Exception('Current password is incorrect.');
        }

        $user->update(['password' => Hash::make($newPassword)]);

        // Logout from all devices
        $user->tokens()->delete();
    }

    /**
     * Reset password (for forgot password)
     */
    public function resetPassword(string $email, string $newPassword): User
    {
        $user = User::where('email', $email)->firstOrFail();

        $user->update(['password' => Hash::make($newPassword)]);

        // Logout from all devices for security
        $user->tokens()->delete();

        return $user;
    }

    /**
     * Get user with all relationships
     */
    public function getUserWithRelations(User $user): User
    {
        return $user->load(['roles', 'profile', 'providerProfile']);
    }
}
