<?php

namespace App\Auth\Handlers;

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

    protected bool $shouldAssignDefaultRole = false;

    /*
     * The AzureSSOService class is responsible for handling the Azure SSO authentication flow
     * It is used to generate the authorization URL and to retrieve the user information
     * */
    protected AzureSSOService $ssoService;

    public function __construct(AzureSSOService $ssoService)
    {
        $this->ssoService = $ssoService;
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

    public function withDefaultRoleAssignment(): self
    {
        $this->shouldAssignDefaultRole = true;

        return $this;
    }

    // PUBLIC FLOWS

    public function getState(): string
    {
        return $this->state;
    }

    public function getAuthorizationUrl(): string
    {
        $this->generateState();

        return $this->ssoService
            ->withState($this->state)
            ->getAuthorizationRedirectUrl();
    }

    /**
     * @throws Exception|UserMissingRoleException
     */
    public function authenticate(): string
    {
        // Parse the state to get the role
        $this->role = $this->extractRoleFromState($this->returnedState);

        $user = $this->findOrCreateUser($this->getAzureUser());

        $this->ensureUserHasRole($user);

        return $this->createAccessToken($user);
    }

    // INTERNAL FUNCTIONS

    private function generateState(): void
    {
        $this->state = Str::random(40).'|'.$this->role;
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

        if ($this->shouldAssignDefaultRole) {
            $user->assignRole($this->role);
        }

        return $user;
    }

    /**
     * @throws Exception
     */
    private function extractRoleFromState(string $state): string
    {
        try {
            return explode('|', $state)[1];
        } catch (Exception $e) {
            throw new Exception('Cannot extract role from state. Invalid state format.');
        }
    }

    /**
     * @throws UserMissingRoleException
     */
    private function ensureUserHasRole(User $user): void
    {

        if ($user->hasRole('super_admin')) {
            return;
        }

        if (! $user->hasRole($this->role)) {
            throw new UserMissingRoleException($this->role);
        }
    }

    public function createAccessToken(User $user): string
    {
        $user->tokens()->delete(); // Only one token per user

        return $user->createToken('access_token')->plainTextToken;
    }
}
