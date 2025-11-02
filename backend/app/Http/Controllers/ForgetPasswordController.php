<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\VerifyResetOtpRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\PasswordResetService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Exception;

class ForgetPasswordController extends Controller
{
    public function __construct(private PasswordResetService $passwordResetService) {}

    public function forgotPassword(ForgotPasswordRequest $request)
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

            $result = $this->passwordResetService->sendPasswordResetOtp($request->validated()['email']);

            RateLimiter::hit($key, 300); // 5 minutes

            return response()->json($result, 200);

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

    public function verifyResetOtp(VerifyResetOtpRequest $request)
    {
        try {
            // Rate limit: 5 attempts per email to prevent brute force
            $key = 'verify-otp:' . $request->validated()['email'];

            if (RateLimiter::tooManyAttempts($key, 5)) {
                $seconds = RateLimiter::availableIn($key);

                return response()->json([
                    'success' => false,
                    'message' => "Too many failed attempts. Please try again in {$seconds} seconds or request a new OTP.",
                ], 429);
            }

            $validated = $request->validated();
            $result = $this->passwordResetService->verifyResetOtp($validated['email'], $validated['otp']);

            // Clear rate limit on success
            RateLimiter::clear($key);

            return response()->json($result, 200);

        } catch (ValidationException $e) {
            $key = 'verify-otp:' . $request->validated()['email'];
            RateLimiter::hit($key, 600); // 10 minutes
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

    public function resetPassword(ResetPasswordRequest $request)
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

            $validated = $request->validated();
            $result = $this->passwordResetService->resetPassword(
                $validated['email'],
                $validated['reset_token'],
                $validated['password']
            );

            // Clear rate limit on success
            RateLimiter::clear($key);

            return response()->json($result, 200);

        } catch (ValidationException $e) {
            RateLimiter::hit('reset-password:' . $request->ip(), 600);
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
