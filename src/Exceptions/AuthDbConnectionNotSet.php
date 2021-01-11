<?php

namespace MDP\Auth\Exceptions;

use Throwable;

class AuthDbConnectionNotSet extends \Exception
{
    public function __construct($message = "Auth wasn\'t provided a proper DB connection to perform this operation.", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}