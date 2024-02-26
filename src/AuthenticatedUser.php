<?php

namespace MDP\Auth;

use MDP\Auth\Authenticatable;

class AuthenticatedUser extends Authenticatable
{
    public function __construct(
        protected int|string $id,
        protected string     $username,
        protected string     $email,
        protected string     $password
    )
    {
    }
}