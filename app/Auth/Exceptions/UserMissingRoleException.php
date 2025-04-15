<?php

namespace App\Auth\Exceptions;

use Exception;

class UserMissingRoleException extends Exception
{
    public const ERROR_CODE = 'user_missing_role';

    public function __construct(string $role)
    {
        $this->message = "User does not have the required role: {$role}";
    }
}
