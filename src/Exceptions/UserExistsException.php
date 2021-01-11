<?php

namespace MDP\Auth\Exceptions;

class UserExistsException extends \Exception
{
    /**
     * UserExistsException constructor.
     */
    public function __construct()
    {
        parent::__construct('A user with that email already exists in our database.');
    }
}
