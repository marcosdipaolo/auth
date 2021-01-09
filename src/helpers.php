<?php

use MDP\Auth\Auth;

if (!function_exists('auth')) {
    function auth(): Auth
    {
        return new Auth();
    }
}