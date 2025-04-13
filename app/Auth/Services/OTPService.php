<?php

namespace App\Auth\Services;

use App\Mail\OTPMail;
use App\Models\UserOTP;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OTPService
{
    /**
     * Generate a 6-digit OTP and store it in the database.
     *
     * @throws ValidationException
     */
    public function generateOTP(string $email, ?string $login_authorization_code = null): UserOTP
    {

        do {
            $otp = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        } while (Str::length($otp) !== 6);

        return UserOTP::query()
            ->updateOrCreate([
                'user_email' => $email,
            ], isset($login_authorization_code) ? [
                'user_email' => $email,
                'otp' => $otp,
                'otp_generated_at' => now(),
                'login_authorization_id' => $login_authorization_code,
            ] : [
                'user_email' => $email,
                'otp' => $otp,
                'otp_generated_at' => now(),
                'login_authorization_id' => $this->generateLoginAuthorizationCode(),
                'last_login_at' => now(),
            ]);
    }

    public function refreshOTP(string $email, string $login_authorization_code): UserOTP
    {
        if (! $this->isEmailAndAuthCodeValid($email, $login_authorization_code)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid authorization code'],
            ]);
        }

        if ($this->isLoginAuthorizationCodeExpired($email, $login_authorization_code)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid authorization code'],
            ]);
        }

        return $this->generateOTP($email, $login_authorization_code);
    }

    public function sendOTPToMail(string $email, string $otp): void
    {
        Mail::to($email)
            ->send(new OTPMail($otp));
    }

    /**
     * @throws ValidationException
     */
    public function verifyOTP(string $email, string $login_authorization_code, string $otp): void
    {

        if (! $this->isEmailAndAuthCodeValid($email, $login_authorization_code)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid authorization code'],
            ]);
        }

        if (! $this->isOTPValid($email, $login_authorization_code, $otp)) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid OTP Given'],
            ]);
        }

        if ($this->isOTPExpired($email, $login_authorization_code, $otp)) {
            throw ValidationException::withMessages([
                'otp' => ['OTP given is already expired. OTP is valid for 5 minutes only.'],
            ]);
        }

        UserOTP::query()
            ->where('otp', $otp)
            ->where('user_email', $email)
            ->where('login_authorization_id', $login_authorization_code)
            ->delete();

    }

    private function isEmailAndAuthCodeValid(string $email, string $login_authorization_code): bool
    {
        return UserOTP::query()
            ->where('login_authorization_id', $login_authorization_code)
            ->where('user_email', $email)
            ->exists();
    }

    private function isLoginAuthorizationCodeExpired(string $email, string $login_authorization_code): bool
    {
        return UserOTP::query()
            ->where('login_authorization_id', $login_authorization_code)
            ->where('user_email', $email)
            ->where('last_login_at', '<', Carbon::now()->subMinutes(5))
            ->exists();
    }

    private function isOTPValid(string $email, string $login_authorization_code, string $otp): bool
    {
        return UserOTP::query()
            ->where('otp', $otp)
            ->where('user_email', $email)
            ->where('login_authorization_id', $login_authorization_code)
            ->exists();
    }

    private function isOTPExpired(string $email, string $login_authorization_code, string $otp): bool
    {

        return UserOTP::query()
            ->where('otp', $otp)
            ->where('user_email', $email)
            ->where('login_authorization_id', $login_authorization_code)
            ->where('otp_generated_at', '<', Carbon::now()->subMinutes(5))
            ->count() > 0;
    }

    private function generateLoginAuthorizationCode(): string
    {
        return Str::random(25);
    }
}
