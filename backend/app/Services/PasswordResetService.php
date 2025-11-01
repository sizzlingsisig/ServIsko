<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetService
{
    public function sendPasswordResetOtp(string $email)
    {
        $user = User::where('email', $email)->first();

        // Always return success to prevent user enumeration
        if ($user) {
            $user->sendOneTimePassword();
        }

        return [
            'success' => true,
            'message' => 'If your email exists in our system, you will receive a password reset OTP.',
        ];
    }

    public function verifyResetOtp(string $email, string $otp)
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['User not found.'],
            ]);
        }

        // Verify OTP
        $result = $user->consumeOneTimePassword($otp);

        if (!$result->isOk()) {
            throw ValidationException::withMessages([
                'otp' => ['The OTP is invalid or has expired.'],
            ]);
        }

        // Generate temporary reset token (valid for 10 minutes)
        $resetToken = Str::random(60);

        Cache::put(
            "password_reset_{$user->id}",
            $resetToken,
            now()->addMinutes(10)
        );

        return [
            'success' => true,
            'message' => 'OTP verified successfully.',
            'reset_token' => $resetToken,
        ];
    }

    public function resetPassword(string $email, string $resetToken, string $password)
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['User not found.'],
            ]);
        }

        // Verify reset token
        $storedToken = Cache::get("password_reset_{$user->id}");

        if (!$storedToken || $storedToken !== $resetToken) {
            throw ValidationException::withMessages([
                'reset_token' => ['Invalid or expired reset token.'],
            ]);
        }

        // Update password
        $user->password = Hash::make($password);
        $user->save();

        // Clear reset token
        Cache::forget("password_reset_{$user->id}");

        // Revoke all existing tokens for security
        $user->tokens()->delete();

        return [
            'success' => true,
            'message' => 'Password reset successful!',
        ];
    }
}
