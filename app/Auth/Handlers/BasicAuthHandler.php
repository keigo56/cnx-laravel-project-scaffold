<?php

namespace App\Auth\Handlers;

use App\Auth\Services\OTPService;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class BasicAuthHandler
{
    protected string $email;

    protected string $password;

    protected string $role;

    protected string $loginAuthorizationId;

    protected string $otpCode;

    protected bool $isMfaEnabled = true;

    protected OTPService $otpService;

    public function __construct(OTPService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function withEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function withLoginAuthorizationId(string $loginAuthorizationId): self
    {
        $this->loginAuthorizationId = $loginAuthorizationId;

        return $this;
    }

    public function withCredentials(string $email, string $password): self
    {
        $this->email = $email;
        $this->password = $password;

        return $this;
    }

    public function withMFACredentials(string $email, string $loginAuthorizationId, string $otpCode): self
    {
        $this->email = $email;
        $this->loginAuthorizationId = $loginAuthorizationId;
        $this->otpCode = $otpCode;

        return $this;
    }

    public function withRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function withMfaEnabled(bool $isMfaEnabled): self
    {
        $this->isMfaEnabled = $isMfaEnabled;

        return $this;
    }

    public function withOtpCode(bool $otpCode): self
    {
        $this->otpCode = $otpCode;

        return $this;
    }

    /**
     * @throws ValidationException
     */
    public function authenticate(): array
    {
        $this->validateCredentials();
        $this->ensureUserHasRole();

        if (! $this->isMfaRequired()) {
            return [
                'mfa_required' => false,
                'email' => $this->email,
                'access_token' => $this->generateAccessToken(),
                'message' => 'MFA is not required. Access token generated.',
            ];
        }

        return $this->initiateMfa();
    }

    public function refreshOtp(): void
    {
        $newUserOTP = $this->otpService->refreshOTP($this->email, $this->loginAuthorizationId);
        $this->otpService->sendOTPToMail($newUserOTP);
    }

    public function verifyOtp(): self
    {
        $this->otpService->verifyOTP(
            $this->email,
            $this->loginAuthorizationId,
            $this->otpCode
        );

        return $this;
    }

    public function finalizeAuthentication(): string
    {
        $this->ensureUserHasRole();

        return $this->generateAccessToken();
    }

    private function isMfaRequired(): bool
    {
        $mfaGloballyEnabled = config('auth.mfa_enabled');

        return $mfaGloballyEnabled && $this->isMfaEnabled;
    }

    /**
     * @throws ValidationException
     */
    private function validateCredentials(): void
    {
        $user = User::where('email', $this->email)->first();

        if (! $user || ! Hash::check(base64_decode($this->password), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
    }

    private function initiateMfa(): array
    {
        $userOtp = $this->otpService->generateOTP($this->email);

        return [
            'mfa_required' => true,
            'email' => $userOtp->email,
            'login_authorization_code' => $userOtp->login_authorization_id,
            'message' => 'MFA required. OTP sent to email.',
        ];
    }

    /**
     * @throws ValidationException
     */
    private function ensureUserHasRole(): void
    {

        $user = User::where('email', $this->email)->first();

        if ($user->hasRole('super_admin')) {
            return;
        }

        if (! $user || ! $user->hasRole($this->role)) {
            throw ValidationException::withMessages([
                'email' => ['The user is not registered with role: '.$this->role],
            ]);
        }
    }

    public function generateAccessToken(): string
    {
        $user = User::query()->where('email', $this->email)->firstOrFail();

        $user->tokens()->delete(); // Only one token per user

        return $user->createToken('access_token')->plainTextToken;
    }
}
