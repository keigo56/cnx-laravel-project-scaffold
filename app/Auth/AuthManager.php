<?php

namespace App\Auth;

class AuthManager
{
    protected array $authConfiguration = [
        'admin' => [
            'auth_flows' => [
                'sso' => [
                    'redirect_path' => '/admin/auth/validate',
                    'should_assign_default_role' => false,
                ],
            ],
            'role_name' => 'Admin',
        ],
        'user' => [
            'auth_flows' => [
                'basic' => [
                    'mfa_enabled' => true,
                ],
            ],
            'role_name' => 'User',
        ],
    ];

    /**
     * Get the appropriate authentication flow based on the role.
     *
     * @throws \Exception
     */
    public function checkIfAuthFlowExistsForRole(string $flow, string $role): void
    {
        if (! isset($this->authConfiguration[$role]['auth_flows'][$flow])) {
            throw new \Exception("Authentication flow {$flow} is not configured for role {$role}.");
        }
    }

    public function getSSORedirectPath(string $role): string
    {
        $redirectPath = $this->authConfiguration[$role]['auth_flows']['sso']['redirect_path'] ?? null;

        if ($redirectPath === null) {
            throw new \Exception("SSO redirect path is not configured for role {$role}.");
        }

        return $redirectPath;
    }

    public function shouldAssignDefaultRole(string $role): bool
    {
        $shouldAssignDefaultRole = $this->authConfiguration[$role]['auth_flows']['sso']['should_assign_default_role'] ?? false;

        return $shouldAssignDefaultRole;
    }

    public function isMfaEnabledForRole(string $role): bool
    {
        $mfaGloballyEnabled = config('auth.mfa_enabled');
        $isMfaEnabled = $this->authConfiguration[$role]['auth_flows']['basic']['mfa_enabled'] ?? true;

        return $mfaGloballyEnabled && $isMfaEnabled;
    }

    public function getRoleName(string $role): string
    {
        $roleName = $this->authConfiguration[$role]['role_name'];

        if ($roleName === null) {
            throw new \Exception("Role name is not configured for role {$role}.");
        }

        return $roleName;
    }
}
