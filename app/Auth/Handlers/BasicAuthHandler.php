<?php

namespace App\Auth\Handlers;

use App\Auth\AuthManager;
use App\Auth\Services\OTPService;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class BasicAuthHandler
{
    protected string $email;

    protected string $password;

    protected string $role;

    protected AuthManager $authManager;

    protected OTPService $otpService;

    public function __construct(AuthManager $authManager, OTPService $otpService)
    {
        $this->authManager = $authManager;
        $this->otpService = $otpService;
    }

    public function withEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function withCredentials(string $email, string $password): self
    {
        $this->email = $email;
        $this->password = $password;

        return $this;
    }

    public function withRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @throws ValidationException
     */
    public function authenticate(): array
    {
        $this->validateCredentials();
        $this->validateIfUserHasRole();

        if (! $this->isMfaRequired()) {
            return $this->provideLoginAccess();
        }

        return $this->handleMfaFlow();
    }

    protected function isMfaRequired(): bool
    {
        return $this->authManager->isMfaEnabledForRole($this->role);
    }

    public function provideLoginAccess(): array
    {
        $this->validateIfUserHasRole();

        $accessToken = $this->createAccessToken($this->email);

        return [
            'success' => true,
            'email' => $this->email,
            'access_token' => $accessToken,
            'message' => 'Login successful',
        ];
    }

    protected function handleMfaFlow(): array
    {
        $userOtp = $this->otpService->generateOTP($this->email);

        return [
            'success' => true,
            'mfa_enabled' => true,
            'email' => $this->email,
            'login_authorization_code' => $userOtp->login_authorization_id,
            'message' => 'MFA required. OTP sent.',
        ];
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

    /**
     * @throws ValidationException
     */
    private function validateIfUserHasRole(): void
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

    public function createAccessToken(string $email): string
    {
        $user = User::query()->where('email', $email)->firstOrFail();

        $user->tokens()->delete(); // Only one token per user

        return $user->createToken('access_token')->plainTextToken;
    }
}
