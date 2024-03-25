<?php

use MDP\Auth\Auth;

if (!function_exists('auth')) {
    function auth(PDO $pdo = null): Auth
    {
        if ($pdo instanceof PDO) {
            return new Auth($pdo);
        }
        return new Auth;
    }
}