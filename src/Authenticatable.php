<?php

namespace MDP\Auth;

abstract class Authenticatable
{
    protected int | string $id;
    protected string $username;
    protected string $email;
    protected string $password;

    public function getAuthenticatedUserId(): int|string
    {
        return $this->id;
    }

    public function getAuthenticatedUsername(): string
    {
        return $this->username;
    }

    public function getAuthenticatedEmail(): string
    {
        return $this->email;
    }

    public function getAuthenticatedHashedPassword(): string
    {
        return $this->password;
    }
}
