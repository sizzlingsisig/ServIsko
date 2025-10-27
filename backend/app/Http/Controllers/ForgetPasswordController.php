<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\RateLimiter;

class ForgetPasswordController extends Controller
{
    public function forgotPassword(Request $request)
    {
        try {
            // Rate limit: 3 attempts per 5 minutes per IP
            $key = 'forgot-password:' . $request->ip();

            if (RateLimiter::tooManyAttempts($key, 3)) {
                $seconds = RateLimiter::availableIn($key);

                return response()->json([
                    'success' => false,
                    'message' => "Too many attempts. Please try again in {$seconds} seconds.",
                ], 429);
            }

            $validated = $request->validate([
                'email' => 'required|email', // Removed exists check for security
            ]);

            $user = User::where('email', $validated['email'])->first();

            // Always return success to prevent user enumeration
            if ($user) {
                // Generate and send OTP via email
                $user->sendOneTimePassword();
            }

            RateLimiter::hit($key, 300); // 5 minutes

            return response()->json([
                'success' => true,
                'message' => 'If your email exists in our system, you will receive a password reset OTP.',
            ], 200);

        } catch (ValidationException $e) {
            throw $e;

        } catch (Exception $e) {
            Log::error('Forgot password error: ' . $e->getMessage(), [
                'email' => $request->email ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send password reset OTP.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function verifyResetOtp(Request $request)
    {
        try {
            // Rate limit: 5 attempts per email to prevent brute force
            $key = 'verify-otp:' . $request->email;

            if (RateLimiter::tooManyAttempts($key, 5)) {
                $seconds = RateLimiter::availableIn($key);

                return response()->json([
                    'success' => false,
                    'message' => "Too many failed attempts. Please try again in {$seconds} seconds or request a new OTP.",
                ], 429);
            }

            $validated = $request->validate([
                'email' => 'required|email',
                'otp' => 'required|string|size:6', // Enforce 6-digit OTP
            ]);

            $user = User::where('email', $validated['email'])->first();

            if (!$user) {
                RateLimiter::hit($key, 600); // 10 minutes

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials.',
                ], 401);
            }

            // Verify OTP
            $result = $user->consumeOneTimePassword($validated['otp']);

            if ($result->isOk()) {
                // Clear rate limit on success
                RateLimiter::clear($key);

                // Generate temporary reset token (valid for 10 minutes)
                $resetToken = Str::random(60);

                Cache::put(
                    "password_reset_{$user->id}",
                    $resetToken,
                    now()->addMinutes(10)
                );

                return response()->json([
                    'success' => true,
                    'message' => 'OTP verified successfully.',
                    'reset_token' => $resetToken,
                ], 200);
            }

            // Increment failed attempts
            RateLimiter::hit($key, 600); // 10 minutes

            return response()->json([
                'success' => false,
                'message' => 'The OTP is invalid or has expired.',
            ], 401);

        } catch (ValidationException $e) {
            throw $e;

        } catch (Exception $e) {
            Log::error('OTP verification error: ' . $e->getMessage(), [
                'email' => $request->email ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'OTP verification failed.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            // Rate limit: 5 attempts per IP
            $key = 'reset-password:' . $request->ip();

            if (RateLimiter::tooManyAttempts($key, 5)) {
                $seconds = RateLimiter::availableIn($key);

                return response()->json([
                    'success' => false,
                    'message' => "Too many attempts. Please try again in {$seconds} seconds.",
                ], 429);
            }

            $validated = $request->validate([
                'email' => 'required|email',
                'reset_token' => 'required|string|size:60',
                'password' => 'required|min:8|confirmed',
            ]);

            $user = User::where('email', $validated['email'])->first();

            if (!$user) {
                RateLimiter::hit($key, 600);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials.',
                ], 401);
            }

            // Verify reset token
            $storedToken = Cache::get("password_reset_{$user->id}");

            if (!$storedToken || $storedToken !== $validated['reset_token']) {
                RateLimiter::hit($key, 600);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired reset token.',
                ], 401);
            }

            // Update password
            $user->password = Hash::make($validated['password']);
            $user->save();

            // Clear reset token
            Cache::forget("password_reset_{$user->id}");

            // Revoke all existing tokens for security
            $user->tokens()->delete();

            // Clear rate limit on success
            RateLimiter::clear($key);

            return response()->json([
                'success' => true,
                'message' => 'Password reset successful!',
            ], 200);

        } catch (ValidationException $e) {
            throw $e;

        } catch (Exception $e) {
            Log::error('Reset password error: ' . $e->getMessage(), [
                'email' => $request->email ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Password reset failed.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
