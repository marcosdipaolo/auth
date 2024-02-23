<?php declare(strict_types=1);

use MDP\Auth\Auth;

class GeneralTest extends \PHPUnit\Framework\TestCase
{
    public Auth $auth;
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->auth = new Auth(new \PDO("sqlite:" . __DIR__ . "/database.sqlite"));
    }

    public function testAuthInstance()
    {
        /**
         * @var mixed $auth
         */
        $auth = new Auth();
        $this->assertInstanceOf(Auth::class, $auth);
        $this->assertInstanceOf(Auth::class, $auth);
    }
}