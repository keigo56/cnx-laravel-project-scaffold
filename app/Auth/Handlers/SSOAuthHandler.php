<?php

namespace App\Auth\Handlers;

use App\Auth\AuthManager;
use App\Auth\Exceptions\UserMissingRoleException;
use App\Auth\Services\AzureSSOService;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;
use Microsoft\Graph\Generated\Models\User as AzureUser;

class SSOAuthHandler
{
    protected string $role;

    /*
     * The state is used to prevent CSRF attacks
     * It is a random string that is generated when the user is redirected to the Azure SSO authorization URL
     * */
    protected string $state;

    protected string $authorizationCode;

    protected string $returnedState;

    protected string $expectedState;

    protected AuthManager $authManager;

    /*
     * The AzureSSOService class is responsible for handling the Azure SSO authentication flow
     * It is used to generate the authorization URL and to retrieve the user information
     * */
    protected AzureSSOService $ssoService;

    public function __construct(AuthManager $authManager, AzureSSOService $ssoService)
    {
        $this->ssoService = $ssoService;
        $this->authManager = $authManager;
    }

    public function withRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function withAuthorizationCode(string $authorizationCode): self
    {
        $this->authorizationCode = $authorizationCode;

        return $this;
    }

    public function withReturnedState(string $returnedState): self
    {
        $this->returnedState = $returnedState;

        return $this;
    }

    public function withExpectedState(string $expectedState): self
    {
        $this->expectedState = $expectedState;

        return $this;
    }

    private function generateState(): void
    {
        $this->state = Str::random(40).'|'.$this->role;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getAuthrorizationURL(): string
    {
        $this->generateState();

        return $this->ssoService
            ->withState($this->state)
            ->getAuthorizationRedirectUrl();
    }

    /**
     * @throws Exception|UserMissingRoleException
     */
    public function authenticate(): array
    {
        // Parse the state to get the role
        $this->role = $this->extractRoleFromState($this->returnedState);

        $azureUser = $this->getAzureUser();
        $user = $this->findOrCreateUser($azureUser);

        $this->validateIfUserHasRole($user);

        $accessToken = $this->createAccessToken($user->email);
        $redirectPath = $this->buildRedirectPath();

        return [
            'access_token' => $accessToken,
            'redirect_path' => $redirectPath,
        ];
    }

    private function getAzureUser(): AzureUser
    {
        return $this->ssoService
            ->withExpectedState($this->expectedState)
            ->withReturnedState($this->returnedState)
            ->withAuthorizationCode($this->authorizationCode)
            ->getUser();
    }

    private function findOrCreateUser(AzureUser $azureUser): User
    {
        $email = $azureUser->getMail();

        $user =
         User::query()->firstOrCreate(
             ['email' => $email],
             ['name' => $azureUser->getDisplayName()]
         );

        if ($this->authManager->shouldAssignDefaultRole($this->role)) {
            $user->assignRole($this->role);
        }

        return $user;
    }

    private function extractRoleFromState(string $state): string
    {
        try {
            return explode('|', $state)[1];
        } catch (\Exception $e) {
            throw new \Exception('Cannot extract role from state. Invalid state format.');
        }
    }

    private function buildRedirectPath(): string
    {
        $frontendBasePath = config('services.azure.frontend_uri');
        $rolePath = $this->authManager->getSSORedirectPath($this->role);

        return $frontendBasePath.$rolePath;
    }

    /**
     * @throws UserMissingRoleException
     */
    private function validateIfUserHasRole(User $user): void
    {

        if ($user->hasRole('super_admin')) {
            return;
        }

        if (! $user->hasRole($this->role)) {
            $redirectPath = $this->buildRedirectPath();
            throw new UserMissingRoleException($this->role, $redirectPath);
        }
    }

    public function createAccessToken(string $email): string
    {
        $user = User::query()->where('email', $email)->firstOrFail();

        $user->tokens()->delete(); // Only one token per user

        return $user->createToken('access_token')->plainTextToken;
    }
}
