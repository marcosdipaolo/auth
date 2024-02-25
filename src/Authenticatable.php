<?php

namespace MDP\Auth;

abstract class Authenticatable
{
    public int | string $id;
    public string $username;
    public string $email;
    public string $password;
}