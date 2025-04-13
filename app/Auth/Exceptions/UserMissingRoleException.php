<?php

namespace App\Auth\Exceptions;

use Exception;

class UserMissingRoleException extends Exception
{
    public const ERROR_CODE = 'user_missing_role';

    public string $redirectPath;

    public function __construct(string $role, string $redirectPath)
    {
        $this->redirectPath = $redirectPath;
        $this->message = "User does not have the required role: {$role}";
    }
}
